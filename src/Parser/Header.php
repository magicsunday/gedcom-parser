<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header as HeaderModel;

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
            if ($this->reader->type() === 'SOUR') {
                //
            }

            if ($this->reader->type() === 'DEST') {
                $header->setDestination($this->reader->value());
            }

            if ($this->reader->type() === 'DATE') {
                $dateParser = new DateExact($this->reader, $this->logger);
                $date       = $dateParser->parse();

                $header->setDate($date);
            }

            if ($this->reader->type() === 'SUBM') {
                $header->setSubmitter($this->reader->value());
            }

            if ($this->reader->type() === 'SUBN') {
                $header->setSubmission($this->reader->value());
            }

            if ($this->reader->type() === 'FILE') {
                $header->setFile($this->reader->value());
            }

            if ($this->reader->type() === 'COPR') {
                $header->setCopyright($this->reader->value());
            }

            if ($this->reader->type() === 'GEDC') {
                //
            }

            if ($this->reader->type() === 'CHAR') {
                //
            }

            if ($this->reader->type() === 'LANG') {
                $header->setLanguage($this->reader->value());
            }

            if ($this->reader->type() === 'PLAC') {
                //
            }

            if ($this->reader->type() === 'NOTE') {
                //
            }
        }

        return $header;
    }
}
