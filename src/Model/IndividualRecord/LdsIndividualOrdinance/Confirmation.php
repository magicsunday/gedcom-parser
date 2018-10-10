<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\ConfirmationInterface;

/**
 * The LDS individual ordinance confirmation (CONL).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Confirmation extends CommonIndividualOrdinance implements ConfirmationInterface
{
    /**
     * @inheritDoc
     */
    public function getDateStatus()
    {
        return $this->getValue(self::TAG_STAT);
    }
}
