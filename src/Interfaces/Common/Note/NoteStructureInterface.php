<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_XREF_NOTE = 'XREF:NOTE';

    /**
     * The note content.
     */
    public const TAG_CONTENT = 'CONTENT';

    /**
     * Returns the pointer to a separate note record.
     *
     * @return string|null
     */
    public function getXref(): ?string;

    /**
     * Returns the note content. Maybe empty if a note pointer is set.
     *
     * @return string|null
     */
    public function getContent(): ?string;
}
