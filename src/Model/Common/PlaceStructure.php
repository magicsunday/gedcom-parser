<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Interfaces\Common\PlaceStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\Note as NoteTrait;

/**
 * The PLAC (place) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PlaceStructure extends DataObject implements PlaceStructureInterface
{
    use NoteTrait;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getValue(self::TAG_PLACE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getFormat()
    {
        return $this->getValue(self::TAG_FORM);
    }

    /**
     * @inheritDoc
     */
    public function getPhoneticVariation()
    {
        return $this->getValue(self::TAG_FONE);
    }

    /**
     * @inheritDoc
     */
    public function getRomanizedVariation()
    {
        return $this->getValue(self::TAG_ROMN);
    }

    /**
     * @inheritDoc
     */
    public function getMap()
    {
        return $this->getValue(self::TAG_MAP);
    }
}
