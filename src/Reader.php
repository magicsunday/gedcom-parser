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
use LogicException;
use RuntimeException;
use SplFileObject;

use function is_string;

/**
 * A GEDCOM file reader.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-reader/
 */
class Reader
{
    /**
     * Regular expression to match the different parts of a line.
     */
    public const PATTERN = '^\s*(\d)\s+(@([^@ ]+)@\s+)?([a-zA-Z_0-9.]+)(\s+@([^@ ]+)@)?(\s(.*))?$';

    /**
     * The matched groups of interest.
     */
    public const MATCH_GROUP_LEVEL = 1;

    public const MATCH_GROUP_ID = 3;

    public const MATCH_GROUP_TAG = 4;

    public const MATCH_GROUP_XREF = 6;

    public const MATCH_GROUP_VALUE = 8;

    /**
     * The file object.
     *
     * @var SplFileObject
     */
    private SplFileObject $file;

    /**
     * The last line read from input.
     *
     * @var bool|string
     */
    private $lastLine;

    /**
     * The last position of the internal file pointer before the next line was read.
     *
     * @var int
     */
    private int $lastPosition;

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
    private string $identifier;

    /**
     * @var string
     */
    private string $tag;

    /**
     * @var string
     */
    private string $xref;

    /**
     * @var string
     */
    private string $value;

    /**
     * Reader constructor.
     *
     * @param string $filename The file to open
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws RuntimeException
     */
    public function __construct(string $filename)
    {
        $this->file = new SplFileObject($filename);

        if (strtoupper($this->file->getExtension()) !== 'GED') {
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
        if (!$this->file->valid()) {
            return false;
        }

        // TODO Use correct GEDCOM char encoding for reading the file

        $this->lastPosition = (int) $this->file->ftell();
        $this->lastLine     = $this->file->fgets();

        ++$this->lineCount;

        if ($this->valid()) {
            // Remove possible BOM from UTF-8 files
            $this->lastLine = trim($this->lastLine, "\xEF\xBB\xBF");

            $matches = [];

            if (preg_match('/' . self::PATTERN . '/s', $this->lastLine, $matches) !== 1) {
                throw new InvalidArgumentException('Unable to match line: <' . trim($this->lastLine) . '>');
            }

            $this->level      = (int) $matches[self::MATCH_GROUP_LEVEL];
            $this->identifier = $matches[self::MATCH_GROUP_ID];
            $this->tag        = $matches[self::MATCH_GROUP_TAG];
            $this->xref       = $matches[self::MATCH_GROUP_XREF];
            $this->value      = $matches[self::MATCH_GROUP_VALUE];

            // Remove line breaks (keep white spaces at the end of lines)
            $this->value = str_replace(["\r", "\n"], '', $this->value);
        }

        return $this->lastLine !== false;
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
     * @return bool|string
     */
    public function current()
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
        return is_string($this->lastLine) && (trim($this->lastLine) !== '');
    }

    /**
     * Moves internal file cursor one element back to the last position.
     *
     * @return bool Returns TRUE on success or FALSE on failure
     */
    public function back(): bool
    {
        return $this->file->fseek($this->lastPosition) === 0;
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
        return $this->identifier;
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
