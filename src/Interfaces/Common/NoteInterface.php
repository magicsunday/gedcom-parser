<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

use MagicSunday\Gedcom\Interfaces\Common\Note\NoteStructureInterface;

/**
 * The NOTE tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface NoteInterface
{
    /**
     * Comments or opinions from the submitter.
     */
    const TAG_NOTE = 'NOTE';

    /**
     * @return null|NoteStructureInterface
     */
    public function getNote();
}
