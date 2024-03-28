<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\CommonChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\CommonDateStatusInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\LdsIndividualOrdinance\CommonDateStatus as CommonDateStatusModel;
use MagicSunday\Gedcom\Parser\Common\DateExact;

/**
 * A FAM (family), SLGS (LDS spouse sealing), STAT (status) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class CommonDateStatus extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            CommonChangeDateInterface::TAG_DATE => DateExact::class,
        ];
    }

    /**
     * Parse a STAT block.
     *
     * @return CommonDateStatusModel
     */
    public function parse(): CommonDateStatusModel
    {
        $status = new CommonDateStatusModel();
        $status->setValue(CommonDateStatusInterface::TAG_DATE_STATUS, $this->reader->value());

        $this->process($status);

        return $status;
    }
}
