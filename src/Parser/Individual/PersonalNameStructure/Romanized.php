<?php
declare(strict_types=1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Parser\Individual\PersonalNameStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Individual\PersonalNameStructure\Romanized as RomanizedName;
use MagicSunday\Gedcom\Parser\Common;

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
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return PersonalNamePieces::getClassMap()
            + [
                RomanizedName::TAG_TYPE => Common::class,
            ];
    }

    /**
     *
     * @return RomanizedName
     */
    public function parse(): RomanizedName
    {
        $romanized = new RomanizedName();
        $romanized->setValue(RomanizedName::TAG_NAME_ROMANIZED_VARIATION, $this->reader->value());

        $this->process($romanized);

        return $romanized;
    }
}
