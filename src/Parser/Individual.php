<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Individual as IndividualModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate;

/**
 * A INDI parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Individual extends AbstractParser
{
    /**
     * Parse a INDI block.
     *
     * @return IndividualModel
     */
    public function parse(): IndividualModel
    {
        $individual = new IndividualModel();
        $individual->setXref($this->reader->identifier());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'NAME':
                    break;

                case 'SEX':
                    break;

                case 'CHAN':
                    $changeDateParser = new ChangeDate($this->reader, $this->logger);
                    $individual->setChangeDate($changeDateParser->parse());
                    break;
            }
        }

        return $individual;
    }
}
