<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\FamilyRecord\LdsSpouseSealing;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\LdsSpouseSealing\SpouseSealingDateStatusInterface;
use MagicSunday\Gedcom\Model\FamilyRecord\LdsSpouseSealing\SpouseSealingDateStatus as SpouseSealingDateStatusModel;
use MagicSunday\Gedcom\Parser\Common\DateExact;

/**
 * A FAM (family), SLGS (LDS spouse sealing), STAT (status) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SpouseSealingDateStatus extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            SpouseSealingDateStatusInterface::TAG_DATE => DateExact::class,
        ];
    }

    /**
     * Parse a STAT block.
     *
     * @return SpouseSealingDateStatusModel
     */
    public function parse(): SpouseSealingDateStatusModel
    {
        $status = new SpouseSealingDateStatusModel();
        $status->setValue(SpouseSealingDateStatusInterface::TAG_DATE_STATUS, $this->reader->value());

        $this->process($status);

        return $status;
    }
}
