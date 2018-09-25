<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Model\Header;

/**
 * A character set structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class CharacterSet
{
    /**
     * A code value that represents the character set to be used to interpret this data. Currently, the
     * preferred character set is ANSEL, which includes ASCII as a subset. UNICODE is not widely
     * supported by most operating systems; therefore, GEDCOM produced using the UNICODE character
     * set will be limited in its interchangeability for a while but should eventually provide the international
     * flexibility that is desired.
     *
     * @var string
     */
    private $characterSet;

    /**
     * An identifier that represents the version level assigned to the associated product. It is defined and
     * changed by the creators of the product.
     *
     * @var string
     */
    private $version;

    /**
     * @return string
     */
    public function getCharacterSet(): string
    {
        return $this->characterSet;
    }

    /**
     * @param string $characterSet
     *
     * @return self
     */
    public function setCharacterSet(string $characterSet): self
    {
        $this->characterSet = $characterSet;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return self
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }
}
