<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\CommonIndividualOrdinanceInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\SealingChildInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinanceInterface;

/**
 * The LDS individual ordinance methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait LdsIndividualOrdinanceTrait
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getValue(string $key);

    /**
     * @return null|CommonIndividualOrdinanceInterface
     */
    public function getLdsBaptism()
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_BAPL);
    }

    /**
     * @return null|CommonIndividualOrdinanceInterface
     */
    public function getLdsConfirmation()
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_CONL);
    }

    /**
     * @return null|CommonIndividualOrdinanceInterface
     */
    public function getLdsEndowment()
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_ENDL);
    }

    /**
     * @return null|SealingChildInterface
     */
    public function getLdsSealingChild()
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_SLGC);
    }
}
