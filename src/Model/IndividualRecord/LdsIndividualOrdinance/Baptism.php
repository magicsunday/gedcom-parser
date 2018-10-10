<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\BaptismInterface;

/**
 * The LDS individual ordinance baptism (BAPL).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Baptism extends CommonIndividualOrdinance implements BaptismInterface
{
    /**
     * @inheritDoc
     */
    public function getDateStatus()
    {
        return $this->getValue(self::TAG_STAT);
    }
}
