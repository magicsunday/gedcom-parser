<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\Interfaces\Common\EventDetailInterface;

/**
 * The family event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface FamilyEventDetailInterface extends EventDetailInterface
{
    /**
     * An individual in the family role of a married man or father.
     */
    const TAG_HUSB = 'HUSB';

    /**
     * An individual in the role as a mother and/or married woman.
     */
    const TAG_WIFE = 'WIFE';

    /**
     * @return null|FamilyPersonAgeInterface
     */
    public function getHusband();

    /**
     * @return null|FamilyPersonAgeInterface
     */
    public function getWife();
}
