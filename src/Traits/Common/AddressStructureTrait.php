<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
trait AddressStructureTrait
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getValue(string $key);

    /**
     * @param string $key
     *
     * @return array
     */
    abstract public function getArrayValue(string $key): array;

    /**
     * @return AddressBlockInterface
     */
    public function getAddress(): AddressBlockInterface
    {
        return $this->getValue(AddressStructureInterface::TAG_ADDR);
    }

    /**
     * @return string[]
     */
    public function getPhoneNumber(): array
    {
        return $this->getArrayValue(AddressStructureInterface::TAG_PHON);
    }

    /**
     * @return string[]
     */
    public function getEmailAddress(): array
    {
        return $this->getArrayValue(AddressStructureInterface::TAG_EMAIL);
    }

    /**
     * @return string[]
     */
    public function getFaxNumber(): array
    {
        return $this->getArrayValue(AddressStructureInterface::TAG_FAX);
    }

    /**
     * @return string[]
     */
    public function getWwwAddress(): array
    {
        return $this->getArrayValue(AddressStructureInterface::TAG_WWW);
    }
}
