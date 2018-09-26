<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Model\NoteInterface;
use MagicSunday\Gedcom\Model\Note;

/**
 * The change date is intended to only record the last change to a record. Some systems may want to
 * manage the change process with more detail, but it is sufficient for GEDCOM purposes to indicate
 * the last time that a record was modified.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ChangeDate implements NoteInterface
{
    /**
     * The date that this data was changed.
     *
     * @var DateExact
     */
    private $date;

    /**
     * A list of assigned notes.
     *
     * @var array
     */
    private $notes = [];

    /**
     * @return DateExact
     */
    public function getDate(): DateExact
    {
        return $this->date;
    }

    /**
     * @param DateExact $date
     *
     * @return self
     */
    public function setDate(DateExact $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return array
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * {@inheritdoc}
     */
    public function addNote(Note $note): NoteInterface
    {
        $this->notes[] = $note;
        return $this;
    }
}
