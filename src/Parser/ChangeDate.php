<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\ChangeDate as ChangeDateModel;

/**
 * A CHAN parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ChangeDate extends AbstractParser
{
    /**
     * Parses a CHAN block.
     *
     * @return ChangeDateModel
     */
    public function parse(): ChangeDateModel
    {
        $changeDate = new ChangeDateModel();

        while ($this->reader->read() && $this->valid()) {
            if ($this->reader->type() === 'DATE') {
                $dateParser = new Date($this->reader, $this->logger);
                $date       = $dateParser->parse();

                $changeDate->setDate($date);
            }

            if ($this->reader->type() === 'NOTE') {
                //
            }
        }

        return $changeDate;
    }
}
