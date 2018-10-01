<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Individual;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Individual\Name\Phonetic;
use MagicSunday\Gedcom\Parser\Individual\Name\Romanized;
use MagicSunday\Gedcom\Model\Individual\Name as NameModel;

/**
 * A individual name parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Name extends AbstractParser
{
    /**
     * PERSONAL_NAME_STRUCTURE
     *
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
            NameModel::TAG_TYPE => Common::class,

            NameModel::TAG_NPFX => Common::class,
            NameModel::TAG_GIVN => Common::class,
            NameModel::TAG_NICK => Common::class,
            NameModel::TAG_SPFX => Common::class,
            NameModel::TAG_SURN => Common::class,
            NameModel::TAG_NSFX => Common::class,
            NameModel::TAG_NOTE => Common\NoteStructure::class,
//            NameModel::TAG_SOUR => Common\SourceCitation::class,

            NameModel::TAG_FONE => Phonetic::class,
            NameModel::TAG_ROMN => Romanized::class,
        ];
    }

    /**
     *
     * @return NameModel
     */
    public function parse(): NameModel
    {
        $name = new NameModel();
        $name->setName($this->reader->value());

        $this->process($name);

//        while ($this->reader->read() && $this->valid()) {
//            switch ($this->reader->type()) {
//                case 'NPFX':
//                    $name->setNamePrefix($this->reader->value());
//                    break;
//
//                case 'GIVN':
//                    $name->setGivenName($this->reader->value());
//                    break;
//
//                case 'NICK':
//                    $name->setNickname($this->reader->value());
//                    break;
//
//                case 'SPFX':
//                    $name->setSurnamePrefix($this->reader->value());
//                    break;
//
//                case 'SURN':
//                    $name->setSurname($this->reader->value());
//                    break;
//
//                case 'NSFX':
//                    $name->setNameSuffix($this->reader->value());
//                    break;
//
//                case 'TYPE':
//                    $name->setType($this->reader->value());
//                    break;
//
//                case 'FONE':
//                    $phoneticParser = new Phonetic($this->reader, $this->logger);
//                    $phonetic       = $phoneticParser->parse();
//
//                    $name->setPhonetic($phonetic);
//                    break;
//
//                case 'ROMN':
//                    $romanizedParser = new Romanized($this->reader, $this->logger);
//                    $romanized       = $romanizedParser->parse();
//
//                    $name->setRomanized($romanized);
//                    break;
//
//                default:
//                    $this->logger->info($this->reader->type());
//            }
//        }

        return $name;
    }
}
