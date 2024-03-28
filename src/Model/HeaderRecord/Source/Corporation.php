<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord\Source;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\CorporationInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\AddressStructureTrait;

/**
 * The corporation structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Corporation extends DataObject implements CorporationInterface
{
    use AddressStructureTrait;

    /**
     * @return string|null
     */
    public function getNameOfBusiness(): ?string
    {
        return $this->getValue(self::TAG_NAME_OF_BUSINESS);
    }
}
