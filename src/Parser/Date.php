<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Date as DateModel;

/**
 * A DATE parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Date extends AbstractParser
{
    /**
     * Parses a DATE block.
     *
     * @return DateModel
     */
    public function parse(): DateModel
    {
        $date = new DateModel($this->reader->value());

        while ($this->reader->read()) { // && $this->valid()) {
//            if ($this->reader->level() !== $this->previousLevel) {
//                $this->reader->back();
//                break;
//            }

            if ($this->reader->type() === 'TIME') {
                $date->setTime($this->reader->value());
            }
        }

        return $date;
    }
}
