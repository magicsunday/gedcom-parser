<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Family as FamilyModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate;

/**
 * A FAM parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Family extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
        ];
    }

    /**
     * Parse a FAM block.
     *
     * @return FamilyModel
     */
    public function parse(): FamilyModel
    {
        $family = new FamilyModel();
        $family->setXref($this->reader->identifier());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'RESN':
                    break;

                // Events
                case 'BIRT':
                    break;

                case 'HUSB':
                    break;

                case 'WIFE':
                    break;

                case 'CHIL':
                    break;

                case 'NCHI':
                    break;

                case 'SUBM':
                    break;

                case 'SLGS':
                    break;

                case 'REFN':
                    break;

                case 'RIN':
                    break;

                case 'CHAN':
                    $changeDateParser = new ChangeDate($this->reader, $this->logger);
                    $family->setChangeDate($changeDateParser->parse());
                    break;

                case 'NOTE':
                    break;

                case 'SOUR':
                    break;

                case 'OBJE':
                    break;
            }
        }

        return $family;
    }
}
