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
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface CorporationInterface extends AddressStructureInterface
{
    /**
     * Name of the business, corporation, or person that produced or commissioned the product.
     */
    public const TAG_NAME_OF_BUSINESS = 'NAME_OF_BUSINESS';

    public function getNameOfBusiness(): ?string;
}
