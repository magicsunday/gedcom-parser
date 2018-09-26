<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header;

use \MagicSunday\Gedcom\Model\Header\Source\Corporation;
use \MagicSunday\Gedcom\Model\Header\Source\Data;

/**
 * The source structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Source
{
    /**
     * A system identification name which was obtained through the GEDCOM registration process. This
     * name must be unique from any other product. Spaces within the name must be substituted with a 0x5F
     * (underscore _) so as to create one word.
     *
     * @var string
     */
    private $systemId;

    /**
     * An identifier that represents the version level assigned to the associated product. It is defined and
     * changed by the creators of the product.
     *
     * @var string
     */
    private $version;

    /**
     * The name of the software product that produced this transmission.
     *
     * @var string
     */
    private $name;

    /**
     * The corporation structure.
     *
     * @var Corporation
     */
    private $corporation;

    /**
     * The data structure.
     *
     * @var Data
     */
    private $data;

    /**
     * @return string
     */
    public function getSystemId(): string
    {
        return $this->systemId;
    }

    /**
     * @param string $systemId
     *
     * @return self
     */
    public function setSystemId(string $systemId): self
    {
        $this->systemId = $systemId;
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
     * @return Corporation
     */
    public function getCorporation(): Corporation
    {
        return $this->corporation;
    }

    /**
     * @param Corporation $corporation
     *
     * @return self
     */
    public function setCorporation(Corporation $corporation): self
    {
        $this->corporation = $corporation;
        return $this;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @param Data $data
     *
     * @return self
     */
    public function setData(Data $data): self
    {
        $this->data = $data;
        return $this;
    }
}
