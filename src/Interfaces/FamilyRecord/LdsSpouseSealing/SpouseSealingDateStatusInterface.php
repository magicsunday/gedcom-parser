<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\FamilyRecord\LdsSpouseSealing;

/**
 * The LDS spouse sealing data status interface.
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
    const TAG_DATE_STATUS = 'LDS_SPOUSE_SEALING_DATE_STATUS';

    /**
     * The date that this data was changed.
     */
    const TAG_DATE = 'DATE';

    /**
     * @return null|string
     */
    public function getStatus();

    /**
     * @return null|string
     */
    public function getChangeDate();
}
