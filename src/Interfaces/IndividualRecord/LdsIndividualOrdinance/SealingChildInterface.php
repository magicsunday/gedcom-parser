<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance;

/**
 * The LDS individual ordinance sealing child (SLGC) interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SealingChildInterface extends CommonIndividualOrdinanceInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    const TAG_FAMC = 'FAMC';

    /**
     * The date status.
     */
    const TAG_STAT = 'STAT';

    /**
     * @return null|string
     */
    public function getFamilyXref();

    /**
     * @return null|SealingChildDateStatusInterface
     */
    public function getDateStatus();
}
