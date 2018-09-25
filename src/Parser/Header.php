<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header as HeaderModel;
use MagicSunday\Gedcom\Parser\Common\DateExact;

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
                    break;

                case 'CHAR':
                    break;

                case 'LANG':
                    $header->setLanguage($this->reader->value());
                    break;

                case 'PLAC':
                    break;

                case 'NOTE':
                    break;
            }
        }

        return $header;
    }
}
