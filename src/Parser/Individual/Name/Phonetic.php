<?php
declare(strict_types = 1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Parser\Individual\Name;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Individual\Name\Phonetic as PhoneticName;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A individual name parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Phonetic extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
            PhoneticName::TAG_NPFX => Common::class,
            PhoneticName::TAG_GIVN => Common::class,
            PhoneticName::TAG_NICK => Common::class,
            PhoneticName::TAG_SPFX => Common::class,
            PhoneticName::TAG_SURN => Common::class,
            PhoneticName::TAG_NSFX => Common::class,
            PhoneticName::TAG_TYPE => Common::class,
        ];
    }


    /**
     * @var PhoneticName
     */
    private $phonetic;

    /**
     *
     * @return PhoneticName
     */
    public function parse(): PhoneticName
    {
        $this->phonetic = new PhoneticName();

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->type()) {
                case 'NPFX':
                    $this->phonetic->setNamePrefix($this->reader->value());
                    break;

                case 'GIVN':
                    $this->phonetic->setGivenName($this->reader->value());
                    break;

                case 'NICK':
                    $this->phonetic->setNickname($this->reader->value());
                    break;

                case 'SPFX':
                    $this->phonetic->setSurnamePrefix($this->reader->value());
                    break;

                case 'SURN':
                    $this->phonetic->setSurname($this->reader->value());
                    break;

                case 'NSFX':
                    $this->phonetic->setNameSuffix($this->reader->value());
                    break;

                case 'TYPE':
                    $this->phonetic->setType($this->reader->value());
                    break;

                default:
                    $this->logger->info($this->reader->type());
            }
        }

        return $this->phonetic;
    }
}
