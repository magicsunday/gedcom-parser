<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\PlaceStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\PlaceStructure\Map as MapModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;

/**
 * A MAP structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Map extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            MapModel::TAG_LATI => Common::class,
            MapModel::TAG_LONG => Common::class,
        ];
    }

    /**
     * Parses a MAP block.
     *
     * @return MapModel
     */
    public function parse(): MapModel
    {
        $map = new MapModel();

        $this->process($map);

        return $map;
    }
}
