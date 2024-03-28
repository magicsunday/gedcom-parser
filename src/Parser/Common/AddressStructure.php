<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\AddressStructure\AddressBlockInterface;
use MagicSunday\Gedcom\Model\Common\AddressStructure\AddressBlock as AddressBlockModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A ADDR parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class AddressStructure extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            AddressBlockInterface::TAG_CONT => Common::class,
            AddressBlockInterface::TAG_ADR1 => Common::class,
            AddressBlockInterface::TAG_ADR2 => Common::class,
            AddressBlockInterface::TAG_ADR3 => Common::class,
            AddressBlockInterface::TAG_CITY => Common::class,
            AddressBlockInterface::TAG_STAE => Common::class,
            AddressBlockInterface::TAG_POST => Common::class,
            AddressBlockInterface::TAG_CTRY => Common::class,
        ];
    }

    /**
     * Parses an ADDR block.
     *
     * @return AddressBlockModel
     */
    public function parse(): AddressBlockModel
    {
        $addressBlock = new AddressBlockModel();
        $addressBlock->setValue(AddressBlockInterface::TAG_ADDRESS_LINE, $this->reader->value());

        $this->process($addressBlock);

        return $addressBlock;
    }
}
