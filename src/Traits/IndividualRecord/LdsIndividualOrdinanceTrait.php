<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
 * @license https://opensource.org/licenses/MIT
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

    public function getLdsBaptism(): ?CommonIndividualOrdinanceInterface
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_BAPL);
    }

    public function getLdsConfirmation(): ?CommonIndividualOrdinanceInterface
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_CONL);
    }

    public function getLdsEndowment(): ?CommonIndividualOrdinanceInterface
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_ENDL);
    }

    public function getLdsSealingChild(): ?SealingChildInterface
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_SLGC);
    }
}
