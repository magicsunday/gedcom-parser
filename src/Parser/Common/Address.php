<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\Address as AddressModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A ADDR parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Address extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
            AddressModel::TAG_CONT => Common::class,
            AddressModel::TAG_ADR1 => Common::class,
            AddressModel::TAG_ADR2 => Common::class,
            AddressModel::TAG_ADR3 => Common::class,
            AddressModel::TAG_CITY => Common::class,
            AddressModel::TAG_STAE => Common::class,
            AddressModel::TAG_POST => Common::class,
            AddressModel::TAG_CTRY => Common::class,
        ];
    }

    /**
     * Parses a ADDR block.
     *
     * @return AddressModel
     */
    public function parse(): AddressModel
    {
        $address = new AddressModel();
        $address->setValue(AddressModel::TAG_ADDRESS_LINE, $this->reader->value());

        $this->process($address);

        return $address;
    }
}
