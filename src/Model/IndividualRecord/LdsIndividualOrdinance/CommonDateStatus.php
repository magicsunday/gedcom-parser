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

/**
 * The LDS individual ordinance date status.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class CommonDateStatus extends CommonChangeDate implements CommonDateStatusInterface
{
    /**
     * {@inheritDoc}
     */
    public function getStatus(): ?string
    {
        return $this->getValue(self::TAG_DATE_STATUS);
    }
}
