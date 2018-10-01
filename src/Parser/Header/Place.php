<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Header;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\Place as PlaceModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A PLAC parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Place extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            PlaceModel::TAG_FORM => Common::class,
        ];
    }

    /**
     * Parses a PLAC block.
     *
     * @return PlaceModel
     */
    public function parse(): PlaceModel
    {
        $place = new PlaceModel();

        $this->process($place);

        return $place;
    }
}
