<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom;

use InvalidArgumentException;
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
     * Delimiter to separate level, type and value in a GEDCOM file.
     */
    const DELIMITER = ' ';

    /**
     * Pointer delimiter.
     */
    const POINTER_DELIMITER = '@';

    /**
     * The file object.
     *
     * @var SplFileObject
     */
    private $file;

    /**
     * The last line read from input.
     *
     * @var string
     */
    private $lastLine;

    /**
     * The last position of the internal file pointer before the next line was read.
     *
     * @var int
     */
    private $lastPosition;

    /**
     * The last line splitted into an array along the delimiter value.
     *
     * @var array
     */
    private $data;

    /**
     * Reader constructor.
     *
     * @param string $filename The file to open
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
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

        $this->lastPosition = $this->file->ftell();
        $this->lastLine     = $this->file->fgets();

        if ($this->lastLine !== false) {
            $this->lastLine = trim($this->lastLine);

            // Ignore empty lines
            if ($this->lastLine === '') {
                return $this->read();
            }

            // Split the line along the delimiter and trim each value
            $this->data = explode(self::DELIMITER, $this->lastLine, 3);
            $this->data = array_map('trim', $this->data);
        }

        return $this->lastLine !== false;
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
     * Returns the normalized identifier pointer (removes the @) if there is one.
     *
     * @return string
     */
    public function identifier(): string
    {
        return trim($this->data[1], self::POINTER_DELIMITER);
    }

    /**
     * Returns the level of the current line.
     *
     * @return int
     */
    public function level(): int
    {
        return (int) $this->data[0];
    }

    /**
     * Returns the type of the current line.
     *
     * @return string
     */
    public function type(): string
    {
        return strtoupper($this->data[1]);
    }

    /**
     * Returns the value of the current line.
     *
     * @return null|string
     */
    public function value()
    {
        return $this->data[2] ?? null;
    }

    /**
     * Returns the current read line (without line breaks).
     *
     * @return string
     */
    public function current(): string
    {
        return $this->lastLine;
    }
}
