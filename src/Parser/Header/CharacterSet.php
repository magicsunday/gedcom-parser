<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Header;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\CharacterSet as CharacterSetModel;

/**
 * A CHAR parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class CharacterSet extends AbstractParser
{
    /**
     * Parses a CHAR block.
     *
     * @return CharacterSetModel
     */
    public function parse(): CharacterSetModel
    {
        $characterSet = new CharacterSetModel();
        $characterSet->setCharacterSet($this->reader->value());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'VERS':
                    $characterSet->setVersion($this->reader->value());
                    break;
            }
        }

        return $characterSet;
    }
}
