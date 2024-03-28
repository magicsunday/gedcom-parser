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
     * @return CommonIndividualOrdinanceInterface|null
     */
    public function getLdsBaptism(): ?CommonIndividualOrdinanceInterface
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_BAPL);
    }

    /**
     * @return CommonIndividualOrdinanceInterface|null
     */
    public function getLdsConfirmation(): ?CommonIndividualOrdinanceInterface
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_CONL);
    }

    /**
     * @return CommonIndividualOrdinanceInterface|null
     */
    public function getLdsEndowment(): ?CommonIndividualOrdinanceInterface
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_ENDL);
    }

    /**
     * @return SealingChildInterface|null
     */
    public function getLdsSealingChild(): ?SealingChildInterface
    {
        return $this->getValue(LdsIndividualOrdinanceInterface::TAG_SLGC);
    }
}
