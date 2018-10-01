<?php
declare(strict_types = 1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Parser\Individual\Name;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Individual\Name\Romanized as RomanizedName;

/**
 * A individual name parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Romanized extends AbstractParser
{
    /**
     * @var RomanizedName
     */
    private $romanized;

    /**
     *
     * @return RomanizedName
     */
    public function parse(): RomanizedName
    {
        $this->romanized = new RomanizedName();

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->type()) {
                case 'NPFX':
                    $this->romanized->setNamePrefix($this->reader->value());
                    break;

                case 'GIVN':
                    $this->romanized->setGivenName($this->reader->value());
                    break;

                case 'NICK':
                    $this->romanized->setNickname($this->reader->value());
                    break;

                case 'SPFX':
                    $this->romanized->setSurnamePrefix($this->reader->value());
                    break;

                case 'SURN':
                    $this->romanized->setSurname($this->reader->value());
                    break;

                case 'NSFX':
                    $this->romanized->setNameSuffix($this->reader->value());
                    break;

                case 'TYPE':
                    $this->romanized->setType($this->reader->value());
                    break;

                default:
                    $this->logger->info($this->reader->type());
            }
        }

        return $this->romanized;
    }
}
