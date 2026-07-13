<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Exception\UnableToParseLineException;
use MagicSunday\Gedcom\Exception\UnsupportedFileException;

use function substr;

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
     * The stream object.
     *
     * @var ReadableStreamInterface
     */
    private ReadableStreamInterface $stream;

    /**
     * The last line read from input.
     *
     * @var string
     */
    private string $lastLine = '';

    /**
     * The last position of the internal file pointer before the next line was read.
     *
     * @var int
     */
    private int $lastPosition = 0;

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
     * @param ReadableStreamInterface $stream
     */
    public function __construct(ReadableStreamInterface $stream)
    {
        $this->stream = $stream;

        if (($stream->getMetadata('stream_type') === 'STDIO')
            && (strtoupper(substr($stream->getMetadata('uri'), -3)) !== 'GED')
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
        if (!$this->stream->isSeekable()) {
            return false;
        }

        // Reset the per-line state so a line missing an identifier, cross-reference or
        // value cannot inherit the previous line's data.
        $this->identifier = '';
        $this->xref       = '';
        $this->value      = '';

        // TODO Use correct GEDCOM char encoding for reading the file

        $this->lastPosition = $this->stream->tell();
        $this->lastLine     = $this->stream->fgets();

        ++$this->lineCount;

        if ($this->valid()) {
            // Remove a leading UTF-8 byte-order mark, once, from the first line only
            if ($this->lineCount === 1) {
                $this->lastLine = $this->stripByteOrderMark($this->lastLine);
            }

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

        return !($this->lastLine === '' && $this->stream->eof());
    }

    /**
     * Removes a leading UTF-8 byte-order mark from the given line, if present.
     *
     * @param string $line The raw line as read from the stream.
     *
     * @return string The line without a leading UTF-8 BOM.
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
     * Moves internal file cursor one element back to the last position.
     *
     * @return bool Returns TRUE on success or FALSE on failure
     */
    public function back(): bool
    {
        return $this->stream->seek($this->lastPosition) === 0;
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
