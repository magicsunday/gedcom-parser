<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header as HeaderModel;
use MagicSunday\Gedcom\Parser\Common\DateExact;
use MagicSunday\Gedcom\Parser\Header\CharacterSet;
use MagicSunday\Gedcom\Parser\Header\GedcomInfo;
use MagicSunday\Gedcom\Parser\Header\Note;
use MagicSunday\Gedcom\Parser\Header\Place;
use MagicSunday\Gedcom\Parser\Header\Source;

/**
 * A HEAD parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Header extends AbstractParser
{
    /**
     * Parses a HEAD block.
     *
     * @return HeaderModel
     */
    public function parse(): HeaderModel
    {
        $header = new HeaderModel();

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->type()) {
                case 'SOUR':
                    $sourceParser = new Source($this->reader, $this->logger);
                    $header->setSource($sourceParser->parse());
                    break;

                case 'DEST':
                    $header->setDestination($this->reader->value());
                    break;

                case 'DATE':
                    $dateParser = new DateExact($this->reader, $this->logger);
                    $header->setDate($dateParser->parse());
                    break;

                case 'SUBM':
                    $header->setSubmitter($this->reader->value());
                    break;

                case 'SUBN':
                    $header->setSubmission($this->reader->value());
                    break;

                case 'FILE':
                    $header->setFile($this->reader->value());
                    break;

                case 'COPR':
                    $header->setCopyright($this->reader->value());
                    break;

                case 'GEDC':
                    $gedcomInfoParser = new GedcomInfo($this->reader, $this->logger);
                    $header->setGedcomInfo($gedcomInfoParser->parse());
                    break;

                case 'CHAR':
                    $charSetParser = new CharacterSet($this->reader, $this->logger);
                    $header->setCharacterSet($charSetParser->parse());
                    break;

                case 'LANG':
                    $header->setLanguage($this->reader->value());
                    break;

                case 'PLAC':
                    $placeParser = new Place($this->reader, $this->logger);
                    $header->setPlace($placeParser->parse());
                    break;

                case 'NOTE':
                    $noteParser = new Note($this->reader, $this->logger);
                    $header->setNote($noteParser->parse());
                    break;
            }
        }

        return $header;
    }
}
