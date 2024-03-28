<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord\LdsSpouseSealing;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\LdsSpouseSealing\SpouseSealingDateStatusInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The LDS spouse sealing data status.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SpouseSealingDateStatus extends DataObject implements SpouseSealingDateStatusInterface
{
    /**
     * {@inheritDoc}
     */
    public function getStatus(): ?string
    {
        return $this->getValue(self::TAG_DATE_STATUS);
    }

    /**
     * {@inheritDoc}
     */
    public function getChangeDate(): ?DateExactInterface
    {
        return $this->getValue(self::TAG_DATE);
    }
}
