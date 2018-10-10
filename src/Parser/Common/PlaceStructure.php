<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\PlaceStructure as PlaceStructureModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure\Map;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure\PlacePhoneticVariation;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure\PlaceRomanizedVariation;

/**
 * A PLAC structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PlaceStructure extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            PlaceStructureModel::TAG_FORM => Common::class,
            PlaceStructureModel::TAG_FONE => PlacePhoneticVariation::class,
            PlaceStructureModel::TAG_ROMN => PlaceRomanizedVariation::class,
            PlaceStructureModel::TAG_MAP  => Map::class,
            PlaceStructureModel::TAG_NOTE => NoteStructure::class,
        ];
    }

    /**
     * Parses a PLAC block.
     *
     * @return PlaceStructureModel
     */
    public function parse(): PlaceStructureModel
    {
        $place = new PlaceStructureModel();
        $place->setValue(PlaceStructureModel::TAG_PLACE_NAME, $this->reader->value());

        $this->process($place);

        return $place;
    }
}
