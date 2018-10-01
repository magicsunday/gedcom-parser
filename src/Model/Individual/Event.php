<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Individual;

use MagicSunday\Gedcom\Model\Common\NoteStructure;

/**
 * The event structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Event
{
    /**
     * A code used to indicate the type of individual event.
     *
     * @var string
     */
    private $type;

    /**
     * The event value.
     *
     * @var null|string
     */
    private $value;

    /**
     * A number that indicates the age in years, months, and days that the principal was at
     * the time of the associated event.
     *
     * @var string
     */
    private $age;

    /**
     * A list of notes.
     *
     * @var NoteStructure[]
     */
    private $notes = [];

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type The type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the value.
     *
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value.
     *
     * @param string $value The value
     *
     * @return self
     */
    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getAge(): string
    {
        return $this->age;
    }

    /**
     * @param string $age
     *
     * @return self
     */
    public function setAge(string $age): self
    {
        $this->age = $age;
        return $this;
    }

    /**
     * @return NoteStructure[]
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * @param NoteStructure $note
     *
     * @return self
     */
    public function addNote(NoteStructure $note): self
    {
        $this->notes[] = $note;
        return $this;
    }
}
