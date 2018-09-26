<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header\Source\Corporation;

/**
 * The address structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Address
{
    /**
     * The address lines usually contain the addressee’s name and other street and city information so that it
     * forms an address that meets mailing requirements.
     *
     * @var string
     */
    private $line;

    /**
     * The first line of the address used for indexing. This is the value of the line corresponding to the
     * ADDR tag line in the address structure.
     *
     * @var string
     */
    private $line1;

    /**
     * The second line of the address used for indexing. This is the value of the first CONT line subordinate
     * to the ADDR tag in the address structure.
     *
     * @var string
     */
    private $line2;

    /**
     * The third line of the address used for indexing. This is the value of the second CONT line subordinate
     * to the ADDR tag in the address structure.
     *
     * @var string
     */
    private $line3;

    /**
     * The name of the city used in the address.
     *
     * @var string
     */
    private $city;

    /**
     * The name of the state used in the address.
     *
     * @var string
     */
    private $state;

    /**
     * The ZIP or postal code used by the various localities in handling of mail.
     *
     * @var string
     */
    private $postalCode;

    /**
     * The name of the country that pertains to the associated address.
     *
     * @var string
     */
    private $country;

    /**
     * @return string
     */
    public function getLine(): string
    {
        return $this->line;
    }

    /**
     * @param string $line
     *
     * @return self
     */
    public function setLine(string $line): self
    {
        $this->line = $line;
        return $this;
    }

    /**
     * @return string
     */
    public function getLine1(): string
    {
        return $this->line1;
    }

    /**
     * @param string $line1
     *
     * @return self
     */
    public function setLine1(string $line1): self
    {
        $this->line1 = $line1;
        return $this;
    }

    /**
     * @return string
     */
    public function getLine2(): string
    {
        return $this->line2;
    }

    /**
     * @param string $line2
     *
     * @return self
     */
    public function setLine2(string $line2): self
    {
        $this->line2 = $line2;
        return $this;
    }

    /**
     * @return string
     */
    public function getLine3(): string
    {
        return $this->line3;
    }

    /**
     * @param string $line3
     *
     * @return self
     */
    public function setLine3(string $line3): self
    {
        $this->line3 = $line3;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return self
     */
    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return self
     */
    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return self
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return self
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }
}
