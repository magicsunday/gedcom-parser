<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Source as SourceModel;

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
     * Parse a SOUR block.
     *
     * @return SourceModel
     */
    public function parse(): SourceModel
    {
        $source = new SourceModel();
        $source->setXref($this->reader->identifier());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
            }
        }

        return $source;
    }
}
