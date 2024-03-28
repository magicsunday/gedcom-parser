<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_NOTE = 'NOTE';

    /**
     * @return NoteStructureInterface[]
     */
    public function getNote(): array;
}
