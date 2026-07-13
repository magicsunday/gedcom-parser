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
use MagicSunday\Gedcom\Exception\UnableToParseLineException;
use Psr\Http\Message\StreamInterface;

use function substr;

/**
 * A GEDCOM file reader.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-reader/
 */
class Reader
{
    /**
     * Regular expression to match the different parts of a line.
     */
    public const PATTERN = '^\s*([1-9]?\d)\s+(@([^@ ]+)@\s+)?([A-Za-z0-9_]+)(\s+@([^@ ]+)@)?(\s(.*))?$';

    /**
     * The matched groups of interest.
     */
    public const MATCH_GROUP_LEVEL = 1;

    public const MATCH_GROUP_ID = 3;

    public const MATCH_GROUP_TAG = 4;

    public const MATCH_GROUP_XREF = 6;

    public const MATCH_GROUP_VALUE = 8;

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
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;

        if (($stream->getMetadata('stream_type') === 'STDIO')
            && (strtoupper(substr($stream->getMetadata('uri'), -3)) !== 'GED')
        ) {
            throw new InvalidArgumentException('Can only read .ged files.');
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
            $this->xref       = $matches[self::MATCH_GROUP_XREF];
            $this->value      = $matches[self::MATCH_GROUP_VALUE];

            // Remove line breaks (keep white spaces at the end of lines)
            $this->value = str_replace(["\r", "\n"], '', $this->value);
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
