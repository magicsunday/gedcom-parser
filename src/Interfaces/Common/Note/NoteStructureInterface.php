<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\Note;

/**
 * The NOTE structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface NoteStructureInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a note record.
     */
    const TAG_XREF_NOTE = 'XREF:NOTE';

    /**
     * The note content.
     */
    const TAG_CONTENT = 'CONTENT';

    /**
     * Returns the pointer to a separate note record.
     *
     * @return null|string
     */
    public function getXref();

    /**
     * Returns the note content. Maybe empty if a note pointer is set.
     *
     * @return null|string
     */
    public function getContent();
}
