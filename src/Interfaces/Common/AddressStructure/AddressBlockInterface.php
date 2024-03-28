<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\AddressStructure;

/**
 * The address block structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface AddressBlockInterface
{
    /**
     * Typically used to define a mailing address of an individual when used subordinate to a RESIdent tag.
     * When it is used subordinate to an event tag, it is the address of the place where the event took place.
     * The address lines usually contain the addressee’s name and other street and city information so that it
     * forms an address that meets mailing requirements.
     */
    public const TAG_ADDRESS_LINE = 'ADDRESS_LINE';

    /**
     * Additional address lines. Used together with TAG_ADDRESS_LINE.
     */
    public const TAG_CONT = 'CONT';

    /**
     * The first line of the address used for indexing. This is the value of the line corresponding to the
     * ADDR tag line in the address structure.
     */
    public const TAG_ADR1 = 'ADR1';

    /**
     * The second line of the address used for indexing. This is the value of the first CONT line subordinate
     * to the ADDR tag in the address structure.
     */
    public const TAG_ADR2 = 'ADR2';

    /**
     * The third line of the address used for indexing. This is the value of the second CONT line subordinate
     * to the ADDR tag in the address structure.
     */
    public const TAG_ADR3 = 'ADR3';

    /**
     * The name of the city used in the address.
     */
    public const TAG_CITY = 'CITY';

    /**
     * The name of the state used in the address.
     */
    public const TAG_STAE = 'STAE';

    /**
     * The ZIP or postal code used by the various localities in handling of mail.
     */
    public const TAG_POST = 'POST';

    /**
     * The name of the country that pertains to the associated address.
     */
    public const TAG_CTRY = 'CTRY';

    /**
     * @return string|null
     */
    public function getAddressLine(): ?string;

    /**
     * @return string[]
     */
    public function getAdditionalLines(): array;

    /**
     * @return string|null
     */
    public function getLine1(): ?string;

    /**
     * @return string|null
     */
    public function getLine2(): ?string;

    /**
     * @return string|null
     */
    public function getLine3(): ?string;

    /**
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * @return string|null
     */
    public function getState(): ?string;

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string;

    /**
     * @return string|null
     */
    public function getCountry(): ?string;
}
