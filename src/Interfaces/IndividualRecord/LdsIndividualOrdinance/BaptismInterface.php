<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance;

/**
 * The LDS individual ordinance baptism (BAPL) interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface BaptismInterface extends CommonIndividualOrdinanceInterface
{
    /**
     * The date status.
     */
    const TAG_STAT = 'STAT';

    /**
     * @return null|CommonDateStatusInterface
     */
    public function getDateStatus();
}
