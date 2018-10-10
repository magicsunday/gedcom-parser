<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance;

/**
 * The LDS individual ordinance date status interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface CommonDateStatusInterface extends CommonChangeDateInterface
{
    /**
     * A code indicating the status of an LDS baptism, confirmation and endowment date:
     *
     * - CHILD
     * - COMPLETED
     * - EXCLUDED
     * - PRE-1970
     * - STILLBORN
     * - SUBMITTED
     * - UNCLEARED
     */
    const TAG_DATE_STATUS = 'DATE_STATUS';

    /**
     * @return null|string
     */
    public function getStatus();
}
