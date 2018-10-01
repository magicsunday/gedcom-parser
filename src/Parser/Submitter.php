<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Submitter as SubmitterModel;
use MagicSunday\Gedcom\Parser\Common\Address;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\ChangeDate;

/**
 * A SUBM parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Submitter extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
        ];
    }

    /**
     * Parses a SUBM block.
     *
     * @return SubmitterModel
     */
    public function parse(): SubmitterModel
    {
        $submitter = new SubmitterModel();
        $submitter->setXref($this->reader->identifier());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'NAME':
                    $submitter->setName($this->reader->value());
                    break;

                case 'ADDR':
                    $addressParser = new Address($this->reader, $this->logger);
                    $submitter->setAddress($addressParser->parse());
                    break;

                case 'PHON':
                    $submitter->addPhoneNumber($this->reader->value());
                    break;

                case 'EMAIL':
                    $submitter->addEmailAddress($this->reader->value());
                    break;

                case 'FAX':
                    $submitter->addFaxNumber($this->reader->value());
                    break;

                case 'WWW':
                    $submitter->addWwwAddress($this->reader->value());
                    break;

                case 'OBJE':
                    break;

                case 'LANG':
                    break;

                case 'REF':
                    break;

                case 'RIN':
                    break;

                case 'NOTE':
                    break;

                case 'CHAN':
                    $changeDateParser = new ChangeDate($this->reader, $this->logger);
                    $submitter->setChangeDate($changeDateParser->parse());
                    break;
            }
        }

        return $submitter;
    }
}
