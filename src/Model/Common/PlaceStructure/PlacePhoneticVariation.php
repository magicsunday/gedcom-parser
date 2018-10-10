<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\PlaceStructure;

use MagicSunday\Gedcom\Interfaces\Common\PlaceStructure\PlacePhoneticVariationInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The place FONE (phonetic) variation tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PlacePhoneticVariation extends DataObject implements PlacePhoneticVariationInterface
{
    /**
     * @inheritDoc
     */
    public function getPlace()
    {
        return $this->getValue(self::TAG_PLACE_PHONETIC_VARIATION);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }
}
