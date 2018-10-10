<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\CommonChangeDateInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The common LDS individual ordinance change date.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class CommonChangeDate extends DataObject implements CommonChangeDateInterface
{
    /**
     * @inheritDoc
     */
    public function getChangeDate()
    {
        return $this->getValue(self::TAG_DATE);
    }
}
