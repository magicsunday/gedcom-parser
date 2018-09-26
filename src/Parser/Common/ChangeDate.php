<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\ChangeDate as ChangeDateModel;

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
            switch ($this->reader->tag()) {
                case 'DATE':
                    $dateParser = new DateExact($this->reader, $this->logger);
                    $changeDate->setDate($dateParser->parse());
                    break;

                case 'NOTE':
                    break;
            }
        }

        return $changeDate;
    }
}
