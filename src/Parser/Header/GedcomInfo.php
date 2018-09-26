<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Header;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\GedcomInfo as GedcomInfoModel;

/**
 * A GEDC parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class GedcomInfo extends AbstractParser
{
    /**
     * Parses a GEDC block.
     *
     * @return GedcomInfoModel
     */
    public function parse(): GedcomInfoModel
    {
        $gedcomInfo = new GedcomInfoModel();

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'VERS':
                    $gedcomInfo->setVersion($this->reader->value());
                    break;

                case 'FORM':
                    $gedcomInfo->setForm($this->reader->value());
                    break;
            }
        }

        return $gedcomInfo;
    }
}
