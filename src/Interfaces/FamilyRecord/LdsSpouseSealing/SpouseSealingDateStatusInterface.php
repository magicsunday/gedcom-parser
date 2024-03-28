<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\FamilyRecord\LdsSpouseSealing;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;

/**
 * The LDS spouse sealing date status interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SpouseSealingDateStatusInterface
{
    /**
     * The spouse sealing date status.
     *
     * - CANCELED
     * - COMPLETED
     * - DNS
     * - EXCLUDED
     * - DNS/CAN
     * - PRE-1970
     * - SUBMITTED
     * - UNCLEARED
     */
    public const TAG_DATE_STATUS = 'LDS_SPOUSE_SEALING_DATE_STATUS';

    /**
     * The date that this data was changed.
     */
    public const TAG_DATE = 'DATE';

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @return DateExactInterface|null
     */
    public function getChangeDate(): ?DateExactInterface;
}
