<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header\Source;

use MagicSunday\Gedcom\Model\Common\Address;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The corporation structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Corporation extends DataObject
{
    /**
     * Name of the business, corporation, or person that produced or commissioned the product.
     */
    const TAG_NAME_OF_BUSINESS = 'NAME_OF_BUSINESS';

    /**
     * Address structure
     */
    const TAG_ADDR = 'ADDR';

    /**
     * A phone number.
     */
    const TAG_PHON = 'PHON';

    /**
     * A phone number.
     */
    const TAG_EMAIL = 'EMAIL';

    /**
     * A phone number.
     */
    const TAG_FAX = 'FAX';

    /**
     * A phone number.
     */
    const TAG_WWW = 'WWW';

    /**
     * @return null|string
     */
    public function getNameOfBusiness()
    {
        return $this->getValue(self::TAG_NAME_OF_BUSINESS);
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->getValue(self::TAG_ADDR);
    }

    /**
     * @return string[]
     */
    public function getPhoneNumbers(): array
    {
        return $this->getValue(self::TAG_PHON);
    }

    /**
     * @return string[]
     */
    public function getEmailAddresses(): array
    {
        return $this->getValue(self::TAG_EMAIL);
    }

    /**
     * @return string[]
     */
    public function getFaxNumbers(): array
    {
        return $this->getValue(self::TAG_FAX);
    }

    /**
     * @return string[]
     */
    public function getWwwAddresses(): array
    {
        return $this->getValue(self::TAG_WWW);
    }
}
