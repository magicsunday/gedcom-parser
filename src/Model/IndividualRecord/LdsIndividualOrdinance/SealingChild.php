<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\SealingChildInterface;

/**
 * The LDS individual ordinance sealing child (SLGC).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SealingChild extends CommonIndividualOrdinance implements SealingChildInterface
{
    /**
     * @inheritDoc
     */
    public function getFamilyXref()
    {
        return $this->getValue(self::TAG_FAMC);
    }
}
