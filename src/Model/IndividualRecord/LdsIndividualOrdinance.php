<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinanceInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The LDS individual ordinance.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class LdsIndividualOrdinance extends DataObject implements LdsIndividualOrdinanceInterface
{
    /**
     * @inheritDoc
     */
    public function getBaptism()
    {
        return $this->getValue(self::TAG_BAPL);
    }

    /**
     * @inheritDoc
     */
    public function getConfirmation()
    {
        return $this->getValue(self::TAG_CONL);
    }

    /**
     * @inheritDoc
     */
    public function getEndowment()
    {
        return $this->getValue(self::TAG_ENDL);
    }

    /**
     * @inheritDoc
     */
    public function getSealingChild()
    {
        return $this->getValue(self::TAG_SLGC);
    }
}
