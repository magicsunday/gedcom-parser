<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetail;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetailInterface;

/**
 * The family MARR (marriage) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface MarriageInterface extends FamilyEventDetailInterface
{
    /**
     * The event detail flag.
     */
    const TAG_FLAG = 'EVENT_FLAG';

    /**
     * @return null|string
     */
    public function getFlag();
}
