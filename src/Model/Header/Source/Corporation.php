<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Model\Header\Source;

use MagicSunday\Gedcom\Model\Header\Source\Corporation\Address;

/**
 * The corporation structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Corporation
{
    /**
     * Name of the business, corporation, or person that produced or commissioned the product.
     *
     * @var string
     */
    private $name;

    /**
     * The address structure.
     *
     * @var Address
     */
    private $address;

    /**
     * A phone number.
     *
     * @var string[]
     */
    private $phone = [];

    /**
     * An electronic address that can be used for contact such as an email address.
     *
     * @var string[]
     */
    private $email = [];

    /**
     * A FAX telephone number appropriate for sending data facsimiles.
     *
     * @var string[]
     */
    private $fax = [];

    /**
     * The world wide web page address.
     *
     * @var string[]
     */
    private $www = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @param Address $address
     *
     * @return self
     */
    public function setAddress(Address $address): self
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getPhoneNumbers(): array
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return self
     */
    public function addPhoneNumber(string $phone): self
    {
        $this->phone[] = $phone;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getEmailAddresses(): array
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public function addEmailAddress(string $email): self
    {
        $this->email[] = $email;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getFaxNumbers(): array
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     *
     * @return self
     */
    public function addFaxNumber(string $fax): self
    {
        $this->fax[] = $fax;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getWwwAddresses(): array
    {
        return $this->www;
    }

    /**
     * @param string $www
     *
     * @return self
     */
    public function addWwwAddress(string $www): self
    {
        $this->www[] = $www;
        return $this;
    }
}