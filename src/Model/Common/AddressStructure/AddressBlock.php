<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
     * {@inheritDoc}
     */
    public function getAddressLine(): ?string
    {
        return $this->getValue(self::TAG_ADDRESS_LINE);
    }

    /**
     * {@inheritDoc}
     */
    public function getAdditionalLines(): array
    {
        return $this->getArrayValue(self::TAG_CONT);
    }

    /**
     * {@inheritDoc}
     */
    public function getLine1(): ?string
    {
        return $this->getValue(self::TAG_ADR1);
    }

    /**
     * {@inheritDoc}
     */
    public function getLine2(): ?string
    {
        return $this->getValue(self::TAG_ADR2);
    }

    /**
     * {@inheritDoc}
     */
    public function getLine3(): ?string
    {
        return $this->getValue(self::TAG_ADR3);
    }

    /**
     * {@inheritDoc}
     */
    public function getCity(): ?string
    {
        return $this->getValue(self::TAG_CITY);
    }

    /**
     * {@inheritDoc}
     */
    public function getState(): ?string
    {
        return $this->getValue(self::TAG_STAE);
    }

    /**
     * {@inheritDoc}
     */
    public function getPostalCode(): ?string
    {
        return $this->getValue(self::TAG_POST);
    }

    /**
     * {@inheritDoc}
     */
    public function getCountry(): ?string
    {
        return $this->getValue(self::TAG_CTRY);
    }
}
