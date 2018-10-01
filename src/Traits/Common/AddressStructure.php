<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\Common;

use MagicSunday\Gedcom\Interfaces\Common\AddressStructure\AddressBlockInterface;
use MagicSunday\Gedcom\Interfaces\Common\AddressStructureInterface;

/**
 * The address structure methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait AddressStructure
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getValue(string $key);

    /**
     * @return null|AddressBlockInterface
     */
    public function getAddress()
    {
        return $this->getValue(AddressStructureInterface::TAG_ADDR);
    }

    /**
     * @return null|string
     */
    public function getPhoneNumber()
    {
        return $this->getValue(AddressStructureInterface::TAG_PHON);
    }

    /**
     * @return null|string
     */
    public function getEmailAddress()
    {
        return $this->getValue(AddressStructureInterface::TAG_EMAIL);
    }

    /**
     * @return null|string
     */
    public function getFaxNumber()
    {
        return $this->getValue(AddressStructureInterface::TAG_FAX);
    }

    /**
     * @return null|string
     */
    public function getWwwAddress()
    {
        return $this->getValue(AddressStructureInterface::TAG_WWW);
    }
}
