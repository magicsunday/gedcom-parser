<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;
use MagicSunday\Gedcom\Model\Common\EventDetail;
use MagicSunday\Gedcom\Traits\Common\AddressStructureTrait;

/**
 * The individual event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualEventDetail extends EventDetail implements IndividualEventDetailInterface
{
    use AddressStructureTrait;

    /**
     * {@inheritDoc}
     */
    public function getAge(): ?string
    {
        return $this->getValue(self::TAG_AGE);
    }
}
