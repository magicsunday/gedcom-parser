<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\CommonDateStatusInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\CommonIndividualOrdinanceInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;

/**
 * The common LDS individual ordinance.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class CommonIndividualOrdinance extends DataObject implements CommonIndividualOrdinanceInterface
{
    use NoteTrait;
    use SourceCitationTrait;

    /**
     * {@inheritDoc}
     */
    public function getDate(): ?string
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getTempleCode(): ?string
    {
        return $this->getValue(self::TAG_TEMP);
    }

    /**
     * {@inheritDoc}
     */
    public function getPlace(): ?string
    {
        return $this->getValue(self::TAG_PLAC);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateStatus(): ?CommonDateStatusInterface
    {
        return $this->getValue(self::TAG_STAT);
    }
}
