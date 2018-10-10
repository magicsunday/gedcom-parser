<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom;

use InvalidArgumentException;
use LogicException;
use RuntimeException;
use SplFileObject;

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
     * Regular expression to match a different parts in a line.
     */
    const PATTERN = '^\s*(\d)\s+(@([^@ ]+)@\s+)?([a-zA-Z_0-9.]+)(\s+@([^@ ]+)@)?(\s(.*))?$';

    /**
     * The matches groups of interest.
     */
    const MATCH_GROUP_LEVEL = 1;
    const MATCH_GROUP_ID    = 3;
    const MATCH_GROUP_TAG   = 4;
    const MATCH_GROUP_XREF  = 6;
    const MATCH_GROUP_VALUE = 8;

    /**
     * The file object.
     *
     * @var SplFileObject
     */
    private $file;

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
    private $lastPosition;

    /**
     * Number of read lines of the file.
     *
     * @var int
     */
    private $lineCount = 0;

    /**
     * @var int
     */
    private $level = 0;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $xref;

    /**
     * @var string
     */
    private $value;

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

        $this->lastPosition = (int) $this->file->ftell();
        $this->lastLine     = $this->file->fgets();

        ++$this->lineCount;

        if ($this->valid()) {
            $matches = [];

            if (preg_match('/' . self::PATTERN . '/s', $this->lastLine, $matches) !== 1) {
                throw new InvalidArgumentException('Unable to match line: <' . trim($this->lastLine) . '>');
            }

            $this->level      = (int) $matches[self::MATCH_GROUP_LEVEL];
            $this->identifier = $matches[self::MATCH_GROUP_ID];
            $this->tag        = $matches[self::MATCH_GROUP_TAG];
            $this->xref       = $matches[self::MATCH_GROUP_XREF];
            $this->value      = $matches[self::MATCH_GROUP_VALUE];

            // Remove line breaks (keep white spaces at end of lines)
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
    public function valid(): bool
    {
        return \is_string($this->lastLine) && (trim($this->lastLine) !== '');
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
     * @return null|string
     */
    public function identifier()
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
     * @return null|string
     */
    public function xref()
    {
        return ($this->xref !== '') ? $this->xref : null;
    }

    /**
     * Returns the value of the current line if there is one.
     *
     * @return null|string
     */
    public function value()
    {
        return ($this->value !== '') ? $this->value : null;
    }
}
