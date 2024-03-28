<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDate\ChangeDateStructureInterface;

/**
 * The CHAN tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface ChangeDateInterface
{
    /**
     * The change date is intended to only record the last change to a record. Some systems may want to
     * manage the change process with more detail, but it is sufficient for GEDCOM purposes to indicate
     * the last time that a record was modified.
     */
    public const TAG_CHAN = 'CHAN';

    /**
     * @return ChangeDateStructureInterface
     */
    public function getChangeDate(): ChangeDateStructureInterface;
}
