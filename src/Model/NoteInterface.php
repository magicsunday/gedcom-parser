<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Model;

/**
 * A note interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface NoteInterface
{
    /**
     * Adds a note to the note list.
     *
     * @param Note $note The note to add
     *
     * @return self
     */
    public function addNote(Note $note): self;
}
