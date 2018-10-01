<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\AddressStructure;

use MagicSunday\Gedcom\Interfaces\Common\AddressStructure\AddressBlockInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The address block structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class AddressBlock extends DataObject implements AddressBlockInterface
{
    /**
     * @inheritDoc
     */
    public function getAddressLine()
    {
        return $this->getValue(self::TAG_ADDRESS_LINE);
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalLines()
    {
        return $this->getValue(self::TAG_CONT);
    }

    /**
     * @inheritDoc
     */
    public function getLine1()
    {
        return $this->getValue(self::TAG_ADR1);
    }

    /**
     * @inheritDoc
     */
    public function getLine2()
    {
        return $this->getValue(self::TAG_ADR2);
    }

    /**
     * @inheritDoc
     */
    public function getLine3()
    {
        return $this->getValue(self::TAG_ADR3);
    }

    /**
     * @inheritDoc
     */
    public function getCity()
    {
        return $this->getValue(self::TAG_CITY);
    }

    /**
     * @inheritDoc
     */
    public function getState()
    {
        return $this->getValue(self::TAG_STAE);
    }

    /**
     * @inheritDoc
     */
    public function getPostalCode()
    {
        return $this->getValue(self::TAG_POST);
    }

    /**
     * @inheritDoc
     */
    public function getCountry()
    {
        return $this->getValue(self::TAG_CTRY);
    }
}
