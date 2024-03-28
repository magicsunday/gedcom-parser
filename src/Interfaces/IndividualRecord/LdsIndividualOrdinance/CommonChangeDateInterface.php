<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;

/**
 * The common LDS individual ordinance change date interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface CommonChangeDateInterface
{
    /**
     * The date that this data was changed.
     */
    public const TAG_DATE = 'DATE';

    /**
     * @return DateExactInterface
     */
    public function getChangeDate(): DateExactInterface;
}
