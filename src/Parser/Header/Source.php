<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Header;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\Source as SourceModel;
use MagicSunday\Gedcom\Parser\Header\Source\Corporation;

/**
 * A SOUR parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Source extends AbstractParser
{
    /**
     * Parses a SOUR block.
     *
     * @return SourceModel
     */
    public function parse(): SourceModel
    {
        $source = new SourceModel();
        $source->setSystemId($this->reader->value());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->type()) {
                case 'VERS':
                    $source->setVersion($this->reader->value());
                    break;

                case 'NAME':
                    $source->setName($this->reader->value());
                    break;

                case 'CORP':
                    $corporationParser = new Corporation($this->reader, $this->logger);
                    $source->setCorporation($corporationParser->parse());
                    break;

                case 'DATA':
                    break;
            }
        }

        return $source;
    }
}
