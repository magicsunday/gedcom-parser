<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\HeaderRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\PlaceInterface;
use MagicSunday\Gedcom\Model\HeaderRecord\Place as PlaceModel;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            PlaceInterface::TAG_FORM => Common::class,
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
