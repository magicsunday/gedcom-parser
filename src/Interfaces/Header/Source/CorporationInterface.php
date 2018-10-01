<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Header\Source;

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
    const TAG_NAME_OF_BUSINESS = 'NAME_OF_BUSINESS';

    /**
     * @return null|string
     */
    public function getNameOfBusiness();
}
