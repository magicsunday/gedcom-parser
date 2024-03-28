<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\HeaderRecord\Source;

use MagicSunday\Gedcom\Interfaces\Common\AddressStructureInterface;

/**
 * The corporation structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface CorporationInterface extends AddressStructureInterface
{
    /**
     * Name of the business, corporation, or person that produced or commissioned the product.
     */
    public const TAG_NAME_OF_BUSINESS = 'NAME_OF_BUSINESS';

    /**
     * @return string|null
     */
    public function getNameOfBusiness(): ?string;
}
