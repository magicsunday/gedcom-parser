<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\PlaceStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\PlaceStructure\PlacePhoneticVariation as PlacePhoneticVariationModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A PLAC structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PlacePhoneticVariation extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            PlacePhoneticVariationModel::TAG_TYPE => Common::class,
        ];
    }

    /**
     * Parses a FONE block.
     *
     * @return PlacePhoneticVariationModel
     */
    public function parse(): PlacePhoneticVariationModel
    {
        $variation = new PlacePhoneticVariationModel();
        $variation->setValue(PlacePhoneticVariationModel::TAG_PLACE_PHONETIC_VARIATION, $this->reader->value());

        $this->process($variation);

        return $variation;
    }
}
