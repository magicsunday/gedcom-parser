<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Header\Source\Corporation;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\Source\Corporation\Address as AddressModel;

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
     * Parses a ADDR block.
     *
     * @return AddressModel
     */
    public function parse(): AddressModel
    {
        $address = new AddressModel();

        $line = $this->reader->value()
            . "\n" . $this->readContent();

        $address->setLine($line);

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'ADR1':
                    $address->setLine1($this->reader->value());
                    break;

                case 'ADR2':
                    $address->setLine2($this->reader->value());
                    break;

                case 'ADR3':
                    $address->setLine3($this->reader->value());
                    break;

                case 'CITY':
                    $address->setCity($this->reader->value());
                    break;

                case 'STAE':
                    $address->setState($this->reader->value());
                    break;

                case 'POST':
                    $address->setPostalCode($this->reader->value());
                    break;

                case 'CTRY':
                    $address->setCountry($this->reader->value());
                    break;
            }
        }

        return $address;
    }
}
