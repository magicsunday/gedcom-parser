<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\DateExact as DateExactModel;

/**
 * A DATE_EXACT parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class DateExact extends AbstractParser
{
    /**
     * Parses a DATE_EXACT block.
     *
     * @return DateExactModel
     */
    public function parse(): DateExactModel
    {
        $date = new DateExactModel($this->reader->value());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->type()) {
                case 'TIME':
                    $date->setTime($this->reader->value());
                    break;
            }
        }

        return $date;
    }
}
