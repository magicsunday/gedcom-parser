<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\PlaceStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\PlaceStructure\PlaceRomanizedVariation as PlaceRomanizedVariationModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A PLAC structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PlaceRomanizedVariation extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            PlaceRomanizedVariationModel::TAG_TYPE => Common::class,
        ];
    }

    /**
     * Parses a ROMN block.
     *
     * @return PlaceRomanizedVariationModel
     */
    public function parse(): PlaceRomanizedVariationModel
    {
        $variation = new PlaceRomanizedVariationModel();
        $variation->setValue(PlaceRomanizedVariationModel::TAG_PLACE_ROMANIZED_VARIATION, $this->reader->value());

        $this->process($variation);

        return $variation;
    }
}
