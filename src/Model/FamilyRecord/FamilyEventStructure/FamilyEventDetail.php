<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetailInterface;
use MagicSunday\Gedcom\Model\Common\EventDetail;
use MagicSunday\Gedcom\Traits\Common\AddressStructureTrait;

/**
 * The family event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyEventDetail extends EventDetail implements FamilyEventDetailInterface
{
    use AddressStructureTrait;

    /**
     * @inheritDoc
     */
    public function getHusband()
    {
        return $this->getValue(self::TAG_HUSB);
    }

    /**
     * @inheritDoc
     */
    public function getWife()
    {
        return $this->getValue(self::TAG_WIFE);
    }
}
