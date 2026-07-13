<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Exception\LineTooLongException;
use MagicSunday\Gedcom\Exception\UnableToParseLineException;
use MagicSunday\Gedcom\Exception\UnsupportedFileException;
use Psr\Http\Message\StreamInterface;

use function array_pop;
use function is_string;
use function preg_match;
use function str_replace;
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
     * Whole lines pushed back by back(), served again before any further stream read (LIFO).
     *
     * @var array<int, string>
     */
    private array $pushback = [];

    /**
     * Whether the underlying stream has been read to its end.
     *
     * @var bool
     */
    private bool $eofReached = false;

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

        // The .ged extension is only meaningful for an actual on-disk file. A non-file STDIO
        // stream (a pipe, a network body) reports a null URI and must not be rejected here.
        $uri = $stream->getMetadata('uri');

        if (($stream->getMetadata('stream_type') === 'STDIO')
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
        // Reset the per-line state so a line missing an identifier, cross-reference or
        // value cannot inherit the previous line's data.
        $this->identifier = '';
        $this->xref       = '';
        $this->value      = '';

        // TODO Use correct GEDCOM char encoding for reading the file

        if ($this->pushback !== []) {
            // A line put back by back() is served again without touching the stream and
            // without advancing the line count.
            $this->lastLine = array_pop($this->pushback);
        } else {
            $line = $this->nextLine();

            ++$this->lineCount;

            // Remove a leading UTF-8 byte-order mark, once, from the first physical line.
            if ($this->lineCount === 1) {
                $line = $this->stripByteOrderMark($line);
            }

            $this->lastLine = $line;
        }

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
                $line         = substr($this->buffer, 0, $end);
                $this->buffer = substr($this->buffer, $end);

                return $line;
            }

            if ($this->eofReached) {
                // No terminator remains; return whatever is left as the final line.
                $line         = $this->buffer;
                $this->buffer = '';

                return $line;
            }

            if (strlen($this->buffer) > self::MAX_LINE_LENGTH) {
                throw new LineTooLongException($this->lineCount + 1, self::MAX_LINE_LENGTH);
            }

            $chunk = $this->stream->read(self::CHUNK_SIZE);

            if ($chunk === '') {
                $this->eofReached = true;
            } else {
                $this->buffer .= $chunk;
            }
        }
    }

    /**
     * Locates the end offset (length to cut) of the terminator of the first line in the
     * buffer. A two-byte terminator (CRLF or LFCR) always wins over a single CR/LF at the
     * same position. A terminator byte at the very end of the buffer is undecidable while
     * more data may follow, so the caller must read another chunk first.
     *
     * @return int|null the number of leading bytes forming the line and its terminator, or
     *                  NULL when no complete, decidable terminator is present yet
     */
    private function locateTerminatorEnd(): ?int
    {
        $index = strcspn($this->buffer, "\r\n");

        if ($index === strlen($this->buffer)) {
            // The buffer holds no terminator byte at all.
            return null;
        }

        // A terminator byte at the very end may be the first half of a CRLF/LFCR pair whose
        // partner is still in the next chunk; wait for more data unless the stream is done.
        if (($index === (strlen($this->buffer) - 1)) && !$this->eofReached) {
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
     * Removes a leading UTF-8 byte-order mark from the given line, if present.
     *
     * @param string $line the raw line as read from the stream
     *
     * @return string the line without a leading UTF-8 BOM
     */
    private function stripByteOrderMark(string $line): string
    {
        if (substr($line, 0, 3) === "\xEF\xBB\xBF") {
            return substr($line, 3);
        }

        return $line;
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
     * former seek-based rewind and therefore works on non-seekable streams too.
     *
     * @return bool Returns TRUE on success or FALSE on failure
     */
    public function back(): bool
    {
        $this->pushback[] = $this->lastLine;

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
