<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\NameRomanizedVariationInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure\NameRomanizedVariation as RomanizedName;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A individual name parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NameRomanizedVariation extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return PersonalNamePieces::getClassMap()
            + [
                NameRomanizedVariationInterface::TAG_TYPE => Common::class,
            ];
    }

    /**
     * @return RomanizedName
     */
    public function parse(): RomanizedName
    {
        $romanized = new RomanizedName();
        $romanized->setValue(NameRomanizedVariationInterface::TAG_NAME_ROMANIZED_VARIATION, $this->reader->value());

        $this->process($romanized);

        return $romanized;
    }
}
