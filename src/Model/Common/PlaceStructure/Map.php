<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\PlaceStructure;

use MagicSunday\Gedcom\Interfaces\Common\PlaceStructure\MapInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The place MAP tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Map extends DataObject implements MapInterface
{
    /**
     * @inheritDoc
     */
    public function getLatitude(): string
    {
        return $this->getValue(self::TAG_LATI);
    }

    /**
     * @inheritDoc
     */
    public function getLongitude(): string
    {
        return $this->getValue(self::TAG_LONG);
    }
}
