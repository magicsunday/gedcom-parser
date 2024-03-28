<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\PlaceStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\PlaceStructure\PlaceRomanizedVariationInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            PlaceRomanizedVariationInterface::TAG_TYPE => Common::class,
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
        $variation->setValue(PlaceRomanizedVariationInterface::TAG_PLACE_ROMANIZED_VARIATION, $this->reader->value());

        $this->process($variation);

        return $variation;
    }
}
