<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Header\Source;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\Source\Corporation as CorporationModel;
use MagicSunday\Gedcom\Parser\Header\Source\Corporation\Address;

/**
 * A CORP parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Corporation extends AbstractParser
{
    /**
     * Parses a CORP block.
     *
     * @return CorporationModel
     */
    public function parse(): CorporationModel
    {
        $corporation = new CorporationModel();
        $corporation->setName($this->reader->value());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'ADDR':
                    $addressParser = new Address($this->reader, $this->logger);
                    $corporation->setAddress($addressParser->parse());
                    break;

                case 'PHON':
                    $corporation->addPhoneNumber($this->reader->value());
                    break;

                case 'EMAIL':
                    $corporation->addEmailAddress($this->reader->value());
                    break;

                case 'FAX':
                    $corporation->addFaxNumber($this->reader->value());
                    break;

                case 'WWW':
                    $corporation->addWwwAddress($this->reader->value());
                    break;
            }
        }

        return $corporation;
    }
}
