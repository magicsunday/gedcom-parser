<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
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
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            AddressBlockModel::TAG_CONT => Common::class,
            AddressBlockModel::TAG_ADR1 => Common::class,
            AddressBlockModel::TAG_ADR2 => Common::class,
            AddressBlockModel::TAG_ADR3 => Common::class,
            AddressBlockModel::TAG_CITY => Common::class,
            AddressBlockModel::TAG_STAE => Common::class,
            AddressBlockModel::TAG_POST => Common::class,
            AddressBlockModel::TAG_CTRY => Common::class,
        ];
    }

    /**
     * Parses a ADDR block.
     *
     * @return AddressBlockModel
     */
    public function parse(): AddressBlockModel
    {
        $addressBlock = new AddressBlockModel();
        $addressBlock->setValue(AddressBlockModel::TAG_ADDRESS_LINE, $this->reader->value());

        $this->process($addressBlock);

        return $addressBlock;
    }
}
