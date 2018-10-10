<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure\FamilyEventDetail;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetail\MarriageInterface;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure\FamilyEventDetail;

/**
 * The family MARR (marriage) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Marriage extends FamilyEventDetail implements MarriageInterface
{
    /**
     * @inheritDoc
     */
    public function getFlag()
    {
        return $this->getValue(self::TAG_FLAG);
    }
}
