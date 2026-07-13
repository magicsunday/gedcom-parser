<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Encoding\AnselDecoder;
use MagicSunday\Gedcom\Exception\LineTooLongException;
use MagicSunday\Gedcom\Exception\UnableToParseLineException;
use MagicSunday\Gedcom\Exception\UnsupportedEncodingException;
use MagicSunday\Gedcom\Exception\UnsupportedFileException;
use Psr\Http\Message\StreamInterface;

use function is_string;
use function mb_convert_encoding;
use function ord;
use function preg_match;
use function str_replace;
use function strcspn;
use function strlen;
use function strncmp;
use function strpos;
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
    private const POINTER_PATTERN = '^@([A-Za-z0-9][^@ ]*)@$';

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
     * The number of bytes read from the underlying stream per chunk.
     */
    private const CHUNK_SIZE = 8192;

    /**
     * The maximum number of leading bytes scanned for the HEAD.CHAR declaration when no BOM
     * decides the encoding. The (required) CHAR field always sits within the header.
     */
    private const CHAR_SNIFF_LIMIT = 65536;

    /**
     * Source encodings the reader transcodes from. ANSEL is the GEDCOM 5.5.1 default.
     */
    private const ENCODING_ANSEL = 'ANSEL';

    private const ENCODING_ASCII = 'ASCII';

    private const ENCODING_UTF8 = 'UTF-8';

    private const ENCODING_UTF16LE = 'UTF-16LE';

    private const ENCODING_UTF16BE = 'UTF-16BE';

    private const ENCODING_CP1252 = 'Windows-1252';

    /**
     * The stream object.
     *
     * @var StreamInterface
     */
    private StreamInterface $stream;

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
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;

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
     * @return bool Returns TRUE on success or FALSE on failure
     */
    public function read(): bool
    {
        // Determine the source encoding once, from the BOM / HEAD.CHAR, before any line is
        // served, so no value is ever decoded under the wrong strategy.
        if ($this->encoding === null) {
            $this->resolveEncoding();
        }

        // Reset the per-line state so a line missing an identifier, cross-reference or
        // value cannot inherit the previous line's data.
        $this->identifier = '';
        $this->xref       = '';
        $this->value      = '';

        if ($this->pushback !== null) {
            // A line put back by back() is served again without touching the stream and
            // without advancing the line count.
            $this->lastLine = $this->pushback;
            $this->pushback = null;
        } else {
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
                }
            }

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
     * @return string the next line including its terminator, or an empty string at the end
     *                of the stream
     *
     * @throws LineTooLongException if a single line exceeds the maximum permitted length
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
     */
    private function pullChunk(): void
    {
        $chunk = $this->stream->read(self::CHUNK_SIZE);

        if ($chunk === '') {
            if ($this->stream->eof()) {
                $this->eofReached = true;
            }

            // A leftover $rawPending here can only be an incomplete UTF-16 tail (a lone byte
            // or an unpaired high surrogate) from a truncated stream; it is dropped rather
            // than emitted as a replacement character.
            return;
        }

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
        if (strncmp($head, "\xEF\xBB\xBF", 3) === 0) {
            $this->bufferOffset += 3;
            $this->encoding = self::ENCODING_UTF8;

            return;
        }

        // UTF-16 by BOM, then the BOM-less null-interleaving heuristic (5.5.1 does not mandate
        // a BOM). The structural framing byte '0' is 0x30 0x00 (LE) / 0x00 0x30 (BE).
        if (strncmp($head, "\xFF\xFE", 2) === 0) {
            $this->beginUtf16(self::ENCODING_UTF16LE, 2);

            return;
        }

        if (strncmp($head, "\xFE\xFF", 2) === 0) {
            $this->beginUtf16(self::ENCODING_UTF16BE, 2);

            return;
        }

        if ((strlen($head) >= 2) && ($head[0] === "\x00") && ($head[1] !== "\x00")) {
            $this->beginUtf16(self::ENCODING_UTF16BE, 0);

            return;
        }

        if ((strlen($head) >= 2) && ($head[1] === "\x00") && ($head[0] !== "\x00")) {
            $this->beginUtf16(self::ENCODING_UTF16LE, 0);

            return;
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
     * @param string $encoding  the resolved UTF-16 endianness constant
     * @param int    $bomLength the number of BOM bytes to drop (0 when detected without a BOM)
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
     * @return string the transcoded UTF-8 bytes
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
     * @param string $unit the two raw bytes of one UTF-16 code unit
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
     * @return string the resolved ENCODING_* constant; ANSEL when no CHAR line is found
     */
    private function sniffCharacterSet(): string
    {
        while (true) {
            $head    = substr($this->buffer, $this->bufferOffset, self::CHAR_SNIFF_LIMIT);
            $matches = [];

            // Capture the whole CHAR value, not just the first token — real exports write
            // multi-word values such as "IBM WINDOWS". Anchor on any of the four GEDCOM
            // terminators (CR, LF, CRLF, LFCR); the /m flag would only recognise LF, so a
            // CR-only file's CHAR line would be missed.
            if (preg_match('/(?:^|[\r\n])[ \t]*\d+[ \t]+CHAR[ \t]+([^\r\n]+)/i', $head, $matches) === 1) {
                return self::normaliseEncoding(trim($matches[1]));
            }

            if ($this->eofReached
                || ((strlen($this->buffer) - $this->bufferOffset) >= self::CHAR_SNIFF_LIMIT)
            ) {
                return self::ENCODING_ANSEL;
            }

            $this->pullChunk();
        }
    }

    /**
     * Maps a HEAD.CHAR value to a source encoding constant.
     *
     * @param string $characterSet the raw CHAR value
     *
     * @return string the matching ENCODING_* constant
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

        // Not 5.5.1 charsets, but common in real Windows exports (ANSI, WINDOWS-1252, and
        // multi-word values like "IBM WINDOWS"): decode as Windows-1252 rather than silently
        // mangling their high bytes as the ANSEL default.
        if (($normalised === 'ANSI')
            || ($normalised === 'CP1252')
            || (strpos($normalised, 'WINDOWS') !== false)
        ) {
            return self::ENCODING_CP1252;
        }

        // Any other unrecognised value falls back to the 5.5.1 default character set.
        return self::ENCODING_ANSEL;
    }

    /**
     * Locates the absolute end offset (one past the terminator) of the first unconsumed line
     * in the buffer. A two-byte terminator (CRLF or LFCR) always wins over a single CR/LF at
     * the same position. A terminator byte at the very end of the buffer is undecidable while
     * more data may follow, so the caller must read another chunk first.
     *
     * @return int|null the absolute buffer offset one past the line's terminator, or NULL when
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
     * @return bool Returns TRUE on success or FALSE on failure
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
     *
     * @return string|null
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
     *
     * @return string|null
     */
    public function xref(): ?string
    {
        return ($this->xref !== '') ? $this->xref : null;
    }

    /**
     * Returns the value of the current line if there is one.
     *
     * @return string|null
     */
    public function value(): ?string
    {
        return ($this->value !== '') ? $this->value : null;
    }
}
