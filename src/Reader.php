<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use InvalidArgumentException;
use MagicSunday\Gedcom\Encoding\AnselDecoder;
use MagicSunday\Gedcom\Exception\InputTooLargeException;
use MagicSunday\Gedcom\Exception\LineTooLongException;
use MagicSunday\Gedcom\Exception\UnableToParseLineException;
use MagicSunday\Gedcom\Exception\UnsupportedEncodingException;
use MagicSunday\Gedcom\Exception\UnsupportedFileException;
use Psr\Http\Message\StreamInterface;

use function iconv;
use function is_string;
use function mb_convert_encoding;
use function ord;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strcspn;
use function strlen;
use function strtoupper;
use function substr;
use function trim;

/**
 * A GEDCOM file reader.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Reader
{
    /**
     * Regular expression to match the different parts of a line.
     */
    public const PATTERN = '^\s*([1-9]?\d)\s+(@([^@ ]+)@\s+)?([A-Za-z0-9_]+)(\s(.*))?$';

    /**
     * Regular expression matching a line value that consists solely of a cross-reference
     * pointer. A pointer starts with an alphanumeric character and occupies the whole
     * value; a @#...@ calendar/charset escape (starting with '#') is therefore not a
     * pointer but text.
     */
    private const string POINTER_PATTERN = '^@([A-Za-z0-9][^@ ]*)@$';

    /**
     * The matched groups of interest.
     */
    public const MATCH_GROUP_LEVEL = 1;

    public const MATCH_GROUP_ID = 3;

    public const MATCH_GROUP_TAG = 4;

    public const MATCH_GROUP_VALUE = 6;

    /**
     * The maximum number of bytes a single physical line may occupy. A terminator-less run
     * longer than this signals a malformed or hostile stream and is rejected instead of
     * being buffered in full, keeping memory usage record-by-record.
     */
    public const MAX_LINE_LENGTH = 65536;

    /**
     * The default maximum number of bytes read from a source before the parse is aborted (see
     * {@see InputTooLargeException} for the threat model). The default (512 MiB) sits above the
     * tens-to-hundreds-of-MB range a legitimate large tree occupies, so it does not reject real data
     * out of the box while still bounding an otherwise unbounded parse. It caps bytes *read*, not the
     * object model the parse builds — which is a multiple of the input size — so a service parsing
     * untrusted input should lower it to roughly its `memory_limit` divided by that expansion factor
     * rather than rely on this ceiling; a caller with a genuinely huge trusted tree may raise it.
     */
    public const int DEFAULT_MAX_BYTES = 512 * 1024 * 1024;

    /**
     * The number of bytes read from the underlying stream per chunk.
     */
    private const int CHUNK_SIZE = 8192;

    /**
     * The maximum number of consecutive empty reads tolerated before a stream that never signals
     * end of stream is treated as ended. Per the PSR-7 read() contract a non-blocking stream may
     * momentarily yield nothing, so a single empty read is retried rather than treated as EOF; this
     * bounds those retries so a stream that yields nothing this many times in a row without ever
     * reporting EOF cannot spin the reader forever (the byte cap cannot catch this, since a stalled
     * stream never advances the byte count). The bound is a spin count, not a time allowance — the
     * retry has no backoff — but the blocking streams this parser consumes never return an empty
     * read before EOF, so they never approach it.
     */
    private const int MAX_CONSECUTIVE_EMPTY_READS = 10000;

    /**
     * The maximum number of leading bytes scanned for the HEAD.CHAR declaration when no BOM
     * decides the encoding. The (required) CHAR field always sits within the header.
     */
    private const int CHAR_SNIFF_LIMIT = 65536;

    /**
     * Core of the HEAD.CHAR line pattern, capturing the whole (possibly multi-word) value up to
     * the line terminator. The terminated match appends a required trailing terminator to this
     * core; the unterminated match, used only at end of stream, matches the bare core.
     */
    private const string CHAR_LINE_PATTERN = '(?:^|[\r\n])[ \t]*\d+[ \t]+CHAR[ \t]+([^\r\n]+)';

    /**
     * Source encodings the reader transcodes from. ANSEL is the GEDCOM 5.5.1 default.
     */
    private const string ENCODING_ANSEL = 'ANSEL';

    private const string ENCODING_ASCII = 'ASCII';

    private const string ENCODING_UTF8 = 'UTF-8';

    private const string ENCODING_UTF16LE = 'UTF-16LE';

    private const string ENCODING_UTF16BE = 'UTF-16BE';

    private const string ENCODING_CP1252 = 'Windows-1252';

    /**
     * The stream object.
     *
     * @var StreamInterface
     */
    private StreamInterface $stream;

    /**
     * The maximum number of bytes that may be read from the stream before the parse is aborted.
     *
     * @var int
     */
    private int $maxBytes;

    /**
     * The running total of bytes read from the stream so far, checked against the cap after each
     * chunk.
     *
     * @var int
     */
    private int $bytesRead = 0;

    /**
     * The number of consecutive empty reads seen since the last read that yielded bytes, used to
     * detect a stream that never signals end of stream.
     *
     * @var int
     */
    private int $consecutiveEmptyReads = 0;

    /**
     * The last line read from input.
     *
     * @var string
     */
    private string $lastLine = '';

    /**
     * Bytes read from the stream but not yet consumed into a line.
     *
     * @var string
     */
    private string $buffer = '';

    /**
     * Offset of the first unconsumed byte in the buffer. Consumed bytes are dropped once per
     * stream read rather than re-sliced on every line, keeping the read path linear.
     *
     * @var int
     */
    private int $bufferOffset = 0;

    /**
     * A line put back by back(), served again by the next read() before any further stream
     * read. Only a single line can be pending, enforced by the canGoBack flag.
     *
     * @var string|null
     */
    private ?string $pushback = null;

    /**
     * Whether the last read line may still be put back. A line can be put back at most once,
     * and only after it has actually been read.
     *
     * @var bool
     */
    private bool $canGoBack = false;

    /**
     * Whether the underlying stream has been read to its end.
     *
     * @var bool
     */
    private bool $eofReached = false;

    /**
     * The resolved source encoding (one of the ENCODING_* constants), or NULL until it has
     * been determined from the BOM / HEAD.CHAR on the first read.
     *
     * @var string|null
     */
    private ?string $encoding = null;

    /**
     * Raw UTF-16 bytes read but not yet transcoded, held so a code unit or surrogate pair
     * split across a chunk boundary is completed by the next read rather than corrupted.
     *
     * @var string
     */
    private string $rawPending = '';

    /**
     * Number of read lines of the file.
     *
     * @var int
     */
    private int $lineCount = 0;

    /**
     * @var int
     */
    private int $level = -1;

    /**
     * @var string
     */
    private string $identifier = '';

    /**
     * @var string
     */
    private string $tag = '';

    /**
     * @var string
     */
    private string $xref = '';

    /**
     * @var string
     */
    private string $value = '';

    /**
     * Reader constructor.
     *
     * @param StreamInterface $stream   The GEDCOM stream to read.
     * @param int|null        $maxBytes The maximum number of bytes to read before aborting, or NULL
     *                                  for {@see self::DEFAULT_MAX_BYTES}. Must be positive.
     *
     * @throws InvalidArgumentException When a non-positive cap is given.
     */
    public function __construct(StreamInterface $stream, ?int $maxBytes = null)
    {
        if (($maxBytes !== null) && ($maxBytes <= 0)) {
            throw new InvalidArgumentException('The maximum byte count must be a positive integer.');
        }

        $this->stream   = $stream;
        $this->maxBytes = $maxBytes ?? self::DEFAULT_MAX_BYTES;

        // The .ged extension is only meaningful for an actual on-disk file (wrapper_type
        // "plainfile"). Every php:// wrapper (stdin, memory, fd) and anonymous pipe carries a
        // non-file wrapper and must be accepted so non-seekable input can be parsed.
        $uri = $stream->getMetadata('uri');

        if (($stream->getMetadata('wrapper_type') === 'plainfile')
            && is_string($uri)
            && (strtoupper(substr($uri, -3)) !== 'GED')
        ) {
            throw new UnsupportedFileException('Can only read .ged files.');
        }
    }

    /**
     * Reads the next line in the document.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function read(): bool
    {
        // Determine the source encoding once, from the BOM / HEAD.CHAR, before any line is
        // served, so no value is ever decoded under the wrong strategy.
        if ($this->encoding === null) {
            $this->resolveEncoding();
        }

        // Reset the per-line state so a line missing an identifier, cross-reference or value —
        // or a skipped blank line — cannot inherit the previous line's data. The level and tag
        // are reset too so their initial or previous values can never leak to a consumer.
        $this->level      = -1;
        $this->identifier = '';
        $this->tag        = '';
        $this->xref       = '';
        $this->value      = '';

        if ($this->pushback !== null) {
            // A line put back by back() is served again without touching the stream and
            // without advancing the line count.
            $this->lastLine = $this->pushback;
            $this->pushback = null;
        } else {
            do {
                $line = $this->nextLine();

                // The empty string signals end of stream, which is not a line and must not be
                // counted.
                if ($line !== '') {
                    ++$this->lineCount;

                    // Transcode the physical line to UTF-8. ANSEL and Windows-1252 both preserve
                    // 0x00-0x7F, so the structural framing is untouched and only value bytes
                    // change; ASCII/UTF-8 pass through (a UTF-8 BOM was consumed by
                    // resolveEncoding()).
                    if ($this->encoding === self::ENCODING_ANSEL) {
                        $line = AnselDecoder::decode($line);
                    } elseif ($this->encoding === self::ENCODING_CP1252) {
                        $decoded = mb_convert_encoding($line, 'UTF-8', self::ENCODING_CP1252);
                        $line    = $decoded !== false ? $decoded : $line;
                    } elseif (($this->encoding !== null) && str_starts_with($this->encoding, 'Windows-')) {
                        // A non-1252 Windows codepage: mbstring's coverage of the 125x range is
                        // partial (it throws for several of them), so transcode via iconv, which
                        // carries the full range; undefined slots are skipped rather than losing
                        // the whole line.
                        $decoded = iconv($this->encoding, self::ENCODING_UTF8 . '//IGNORE', $line);
                        $line    = $decoded !== false ? $decoded : $line;
                    }
                }

                // A blank or whitespace-only line carries no level or tag, so serving it would
                // leave the structural state undefined; skip it and read on. The counter still
                // advanced above so error line numbers stay accurate. The empty end-of-stream
                // marker breaks the loop.
            } while (($line !== '') && (trim($line) === ''));

            $this->lastLine = $line;
        }

        // A real line just became current and may be put back exactly once; the end-of-stream
        // empty line cannot.
        $this->canGoBack = $this->lastLine !== '';

        if ($this->valid()) {
            $matches = [];

            if (preg_match('/' . self::PATTERN . '/s', $this->lastLine, $matches) !== 1) {
                throw new UnableToParseLineException($this->lastLine, $this->lineCount);
            }

            $this->level      = (int) $matches[self::MATCH_GROUP_LEVEL];
            $this->identifier = $matches[self::MATCH_GROUP_ID];
            $this->tag        = $matches[self::MATCH_GROUP_TAG];

            // Remove line breaks (keep white spaces at the end of lines)
            $rawValue = str_replace(["\r", "\n"], '', $matches[self::MATCH_GROUP_VALUE] ?? '');

            // A line value is EITHER a single cross-reference pointer (first character
            // alphanumeric, occupying the whole value) OR text. A @#...@ calendar or
            // character-set escape is text, not a pointer, so it stays in the value.
            if (preg_match('/' . self::POINTER_PATTERN . '/', $rawValue, $pointer) === 1) {
                $this->xref = $pointer[1];
            } else {
                // Decode the doubled-@ escape: a literal @ inside a value is written @@.
                $this->value = str_replace('@@', '@', $rawValue);
            }
        }

        return $this->lastLine !== '';
    }

    /**
     * Reads and returns the next physical line from the stream, including its terminator,
     * splitting the internal buffer on any of the four GEDCOM 5.5.1 terminators (CR, LF,
     * CRLF, LFCR).
     *
     * @return string The next line including its terminator, or an empty string at the end
     *                of the stream
     *
     * @throws LineTooLongException   If a single line exceeds the maximum permitted length.
     * @throws InputTooLargeException When the source exceeds the configured byte cap.
     */
    private function nextLine(): string
    {
        while (true) {
            $end = $this->locateTerminatorEnd();

            if ($end !== null) {
                if (($end - $this->bufferOffset) > self::MAX_LINE_LENGTH) {
                    throw new LineTooLongException($this->lineCount + 1, self::MAX_LINE_LENGTH);
                }

                $line               = substr($this->buffer, $this->bufferOffset, $end - $this->bufferOffset);
                $this->bufferOffset = $end;

                return $line;
            }

            if ($this->eofReached) {
                // No terminator remains; return whatever is left as the final line.
                $line               = substr($this->buffer, $this->bufferOffset);
                $this->buffer       = '';
                $this->bufferOffset = 0;

                return $line;
            }

            if ((strlen($this->buffer) - $this->bufferOffset) > self::MAX_LINE_LENGTH) {
                throw new LineTooLongException($this->lineCount + 1, self::MAX_LINE_LENGTH);
            }

            // Drop already-consumed bytes once per stream read instead of re-slicing the
            // buffer on every line.
            if ($this->bufferOffset > 0) {
                $this->buffer       = substr($this->buffer, $this->bufferOffset);
                $this->bufferOffset = 0;
            }

            $this->pullChunk();
        }
    }

    /**
     * Reads one chunk from the stream into the buffer. Per PSR-7 an empty read means "no
     * bytes available", which is end of stream only once eof() confirms it; a non-blocking or
     * slow stream may momentarily yield nothing, so the caller retries rather than treating an
     * empty read as EOF.
     *
     * @return void
     *
     * @throws InputTooLargeException When the source exceeds the configured byte cap.
     */
    private function pullChunk(): void
    {
        $chunk = $this->stream->read(self::CHUNK_SIZE);

        // Count the raw bytes actually read from the source — pre-transcode, so a UTF-16 or ANSEL
        // stream is bounded on its true source size — and abort once the cap is passed. This is the
        // single choke point every read path funnels through, so one check bounds them all.
        $this->bytesRead += strlen($chunk);

        if ($this->bytesRead > $this->maxBytes) {
            throw new InputTooLargeException($this->maxBytes);
        }

        if ($chunk === '') {
            // An empty read means end of stream once eof() confirms it. Otherwise it is a
            // momentary no-data yield from a non-blocking or slow stream and is retried — but
            // only up to a bounded number of consecutive times, so a stream that never signals
            // EOF is treated as ended rather than spinning the reader forever.
            if ($this->stream->eof()
                || (++$this->consecutiveEmptyReads >= self::MAX_CONSECUTIVE_EMPTY_READS)
            ) {
                $this->eofReached = true;
            }

            // A leftover $rawPending here can only be an incomplete UTF-16 tail (a lone byte
            // or an unpaired high surrogate) from a truncated stream; it is dropped rather
            // than emitted as a replacement character.
            return;
        }

        // A read that yielded bytes resets the stalled-stream counter.
        $this->consecutiveEmptyReads = 0;

        // UTF-16 is transcoded to UTF-8 before it enters the buffer, so the terminator scan
        // and every downstream step operate on single-byte UTF-8.
        if (($this->encoding === self::ENCODING_UTF16LE) || ($this->encoding === self::ENCODING_UTF16BE)) {
            $this->rawPending .= $chunk;
            $this->buffer .= $this->drainUtf16();

            return;
        }

        $this->buffer .= $chunk;
    }

    /**
     * Determines the source encoding from a leading BOM or the HEAD.CHAR declaration and
     * consumes a UTF-8 BOM. Runs once, before the first line is served.
     *
     * @return void
     */
    private function resolveEncoding(): void
    {
        // Prime enough bytes to test for a BOM.
        while (((strlen($this->buffer) - $this->bufferOffset) < 3) && !$this->eofReached) {
            $this->pullChunk();
        }

        $head = substr($this->buffer, $this->bufferOffset);

        // Consume a leading UTF-8 BOM once; there is no per-line BOM handling downstream.
        if (str_starts_with($head, "\xEF\xBB\xBF")) {
            $this->bufferOffset += 3;
            $this->encoding = self::ENCODING_UTF8;

            return;
        }

        // UTF-16 by BOM, then the BOM-less null-interleaving heuristic (5.5.1 does not mandate
        // a BOM). The structural framing byte '0' is 0x30 0x00 (LE) / 0x00 0x30 (BE).
        if (str_starts_with($head, "\xFF\xFE")) {
            $this->beginUtf16(self::ENCODING_UTF16LE, 2);

            return;
        }

        if (str_starts_with($head, "\xFE\xFF")) {
            $this->beginUtf16(self::ENCODING_UTF16BE, 2);

            return;
        }

        // BOM-less UTF-16: the structural '0'/'1' level byte is ASCII, so one half of the first
        // code unit is a null. The shared length guard avoids an offset warning on a single-byte
        // stream; which half is null decides the endianness.
        if (strlen($head) >= 2) {
            if (($head[0] === "\x00") && ($head[1] !== "\x00")) {
                $this->beginUtf16(self::ENCODING_UTF16BE, 0);

                return;
            }

            if (($head[1] === "\x00") && ($head[0] !== "\x00")) {
                $this->beginUtf16(self::ENCODING_UTF16LE, 0);

                return;
            }
        }

        // A single-byte, ASCII-structured stream (ANSEL/ASCII/UTF-8): sniff the required CHAR
        // field on the raw bytes (the level/tag framing is 0x00-0x7F under every candidate),
        // defaulting to ANSEL (the 5.5.1 default) when it is absent.
        $this->encoding = $this->sniffCharacterSet();
    }

    /**
     * Switches to UTF-16 decoding: consumes the byte-order mark and moves the bytes already
     * buffered while sniffing into the transcode pipe.
     *
     * @param string $encoding  The resolved UTF-16 endianness constant.
     * @param int    $bomLength The number of BOM bytes to drop (0 when detected without a BOM)
     *
     * @return void
     */
    private function beginUtf16(string $encoding, int $bomLength): void
    {
        $this->encoding     = $encoding;
        $this->rawPending   = substr($this->buffer, $this->bufferOffset + $bomLength);
        $this->buffer       = $this->drainUtf16();
        $this->bufferOffset = 0;
    }

    /**
     * Transcodes the complete-scalar prefix of the pending UTF-16 bytes to UTF-8, holding back
     * a trailing partial code unit or a lone high surrogate so a character split across a
     * chunk boundary is completed by the next read.
     *
     * @return string The transcoded UTF-8 bytes.
     */
    private function drainUtf16(): string
    {
        $length = strlen($this->rawPending);
        $take   = $length - ($length % 2);

        if (($take >= 2) && $this->isHighSurrogate(substr($this->rawPending, $take - 2, 2))) {
            $take -= 2;
        }

        if ($take === 0) {
            return '';
        }

        $complete         = substr($this->rawPending, 0, $take);
        $this->rawPending = substr($this->rawPending, $take);

        $result = mb_convert_encoding($complete, 'UTF-8', (string) $this->encoding);

        return $result !== false ? $result : '';
    }

    /**
     * Whether the given two-byte UTF-16 code unit (in the current endianness) is a high
     * surrogate, i.e. the first half of an astral-character surrogate pair.
     *
     * @param string $unit The two raw bytes of one UTF-16 code unit.
     *
     * @return bool
     */
    private function isHighSurrogate(string $unit): bool
    {
        $codeUnit = $this->encoding === self::ENCODING_UTF16LE
            ? (ord($unit[0]) | (ord($unit[1]) << 8))
            : ((ord($unit[0]) << 8) | ord($unit[1]));

        return ($codeUnit >= 0xD800) && ($codeUnit <= 0xDBFF);
    }

    /**
     * Scans the buffered header for the HEAD.CHAR declaration and maps it to a source
     * encoding, reading further chunks up to CHAR_SNIFF_LIMIT bytes. Does not consume the
     * buffer — the header is still tokenised normally afterwards.
     *
     * @return string The resolved ENCODING_* constant; ANSEL when no CHAR line is found.
     */
    private function sniffCharacterSet(): string
    {
        while (true) {
            $head    = substr($this->buffer, $this->bufferOffset, self::CHAR_SNIFF_LIMIT);
            $matches = [];

            // Capture the whole CHAR value, not just the first token — real exports write
            // multi-word values such as "IBM WINDOWS". Anchor on any of the four GEDCOM
            // terminators (CR, LF, CRLF, LFCR); the /m flag would only recognise LF, so a
            // CR-only file's CHAR line would be missed. Require a trailing terminator so a
            // value split across a chunk boundary is not accepted while still truncated.
            if (preg_match('/' . self::CHAR_LINE_PATTERN . '[\r\n]/i', $head, $matches) === 1) {
                return self::normaliseEncoding(trim($matches[1]));
            }

            if ($this->eofReached) {
                // At the true end of the stream the CHAR value may legitimately be the last,
                // unterminated line — accept it without a trailing terminator.
                if (preg_match('/' . self::CHAR_LINE_PATTERN . '/i', $head, $matches) === 1) {
                    return self::normaliseEncoding(trim($matches[1]));
                }

                return self::ENCODING_ANSEL;
            }

            if ((strlen($this->buffer) - $this->bufferOffset) >= self::CHAR_SNIFF_LIMIT) {
                // Buffered the full sniff window without a terminated CHAR line. The EOF flag
                // lags a read behind the buffer, so a stream that ends exactly on the cap has
                // not set it yet. Pull exactly once — never in a loop, so a non-blocking stream
                // that never signals EOF cannot spin the sniff — then settle the outcome.
                $bufferedBytes = strlen($this->buffer);
                $this->pullChunk();

                if (strlen($this->buffer) > $bufferedBytes) {
                    // Further bytes arrived: the CHAR value overruns the sniff window. Give up
                    // rather than accept a value that may still be truncated.
                    return self::ENCODING_ANSEL;
                }

                if ($this->eofReached) {
                    // The stream ended exactly on the cap; re-enter so the EOF branch resolves
                    // a complete, unterminated final CHAR line.
                    continue;
                }

                // Neither grew nor reached EOF (a non-blocking stream momentarily without
                // data): keep the sniff bounded by the cap and fall back to the default.
                return self::ENCODING_ANSEL;
            }

            $this->pullChunk();
        }
    }

    /**
     * Maps a HEAD.CHAR value to a source encoding constant.
     *
     * @param string $characterSet The raw CHAR value.
     *
     * @return string The matching ENCODING_* constant.
     */
    private static function normaliseEncoding(string $characterSet): string
    {
        $normalised = strtoupper($characterSet);

        switch ($normalised) {
            case 'UTF-8':
            case 'UTF8':
                return self::ENCODING_UTF8;

            case 'ASCII':
                return self::ENCODING_ASCII;

            case 'ANSEL':
                return self::ENCODING_ANSEL;

            case 'UNICODE':
                // A byte-framed stream that declares UNICODE (UTF-16) has no BOM and did not
                // trigger the null heuristic, so it is undetectable/contradictory — reject it
                // rather than silently mis-parsing.
                throw new UnsupportedEncodingException($characterSet);
        }

        // A named single-byte Windows codepage (WINDOWS-1250, WINDOWS1251, CP1257, …) decodes with
        // that exact codepage when the platform's iconv carries it; an unsupported number falls
        // back to the Windows-1252 default rather than mangling the high bytes. The match is limited
        // to the single-byte 125x family: those are ASCII-transparent over 0x00-0x7F, so the
        // structural framing survives the per-line decode — multibyte Windows codepages (932, 936,
        // …) are not, and their trail bytes would collide with the line terminator scan. GEDCOM
        // 5.5.1 defines no Windows codepages, so this is a lenient real-world convenience, not a
        // conformance requirement.
        if (preg_match('/^(?:CP|WINDOWS-?)(125\d)$/', $normalised, $matches) === 1) {
            $codepage = 'Windows-' . $matches[1];

            return self::iconvSupportsEncoding($codepage) ? $codepage : self::ENCODING_CP1252;
        }

        // Not 5.5.1 charsets, but common in real Windows exports (ANSI and a bare, codepage-less
        // WINDOWS / "IBM WINDOWS"): decode as Windows-1252, the correct default for that ambiguous
        // case, rather than silently mangling their high bytes as the ANSEL default.
        if (($normalised === 'ANSI')
            || str_contains($normalised, 'WINDOWS')
        ) {
            return self::ENCODING_CP1252;
        }

        // Any other unrecognised value falls back to the 5.5.1 default character set.
        return self::ENCODING_ANSEL;
    }

    /**
     * Reports whether the platform's iconv carries the given encoding, probing with a single high
     * byte. An unsupported encoding makes iconv return FALSE; the probe's warning is swallowed so
     * the detection stays silent.
     *
     * @param string $encoding The candidate iconv encoding name.
     *
     * @return bool Whether iconv can transcode from the encoding.
     */
    private static function iconvSupportsEncoding(string $encoding): bool
    {
        set_error_handler(static fn (): bool => true);

        try {
            return iconv($encoding, self::ENCODING_UTF8, "\xB3") !== false;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Locates the absolute end offset (one past the terminator) of the first unconsumed line
     * in the buffer. A two-byte terminator (CRLF or LFCR) always wins over a single CR/LF at
     * the same position. A terminator byte at the very end of the buffer is undecidable while
     * more data may follow, so the caller must read another chunk first.
     *
     * @return int|null The absolute buffer offset one past the line's terminator, or NULL when
     *                  no complete, decidable terminator is present yet
     */
    private function locateTerminatorEnd(): ?int
    {
        $length = strlen($this->buffer);
        $index  = $this->bufferOffset + strcspn($this->buffer, "\r\n", $this->bufferOffset);

        if ($index === $length) {
            // The unconsumed buffer holds no terminator byte at all.
            return null;
        }

        // A terminator byte at the very end may be the first half of a CRLF/LFCR pair whose
        // partner is still in the next chunk; wait for more data unless the stream is done.
        if (($index === ($length - 1)) && !$this->eofReached) {
            return null;
        }

        $first  = $this->buffer[$index];
        $second = $this->buffer[$index + 1] ?? '';

        if ((($first === "\r") && ($second === "\n"))
            || (($first === "\n") && ($second === "\r"))
        ) {
            return $index + 2;
        }

        return $index + 1;
    }

    /**
     * Returns the number of read lines.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->lineCount;
    }

    /**
     * Returns the current read line.
     *
     * @return string
     */
    public function current(): string
    {
        return $this->lastLine;
    }

    /**
     * Returns TRUE if the last read line is not empty.
     *
     * @return bool
     */
    private function valid(): bool
    {
        return trim($this->lastLine) !== '';
    }

    /**
     * Puts the last read line back so the next read() serves it again. This replaces the
     * former seek-based rewind and therefore works on non-seekable streams too. It is a no-op
     * (returning FALSE) before the first read, at the end of the stream, or when called twice
     * without an intervening read.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function back(): bool
    {
        if (!$this->canGoBack) {
            return false;
        }

        $this->pushback  = $this->lastLine;
        $this->canGoBack = false;

        return true;
    }

    /**
     * Returns the level of the current line.
     *
     * @return int
     */
    public function level(): int
    {
        return $this->level;
    }

    /**
     * Returns the identifier pointer if there is one.
     */
    public function identifier(): ?string
    {
        return ($this->identifier !== '') ? $this->identifier : null;
    }

    /**
     * Returns the tag of the current line.
     *
     * @return string
     */
    public function tag(): string
    {
        return $this->tag;
    }

    /**
     * Returns the xref of the current line if there is one.
     */
    public function xref(): ?string
    {
        return ($this->xref !== '') ? $this->xref : null;
    }

    /**
     * Returns the value of the current line if there is one.
     */
    public function value(): ?string
    {
        return ($this->value !== '') ? $this->value : null;
    }
}
