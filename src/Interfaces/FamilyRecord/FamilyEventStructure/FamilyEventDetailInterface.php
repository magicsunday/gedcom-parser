<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_HUSB = 'HUSB';

    /**
     * An individual in the role as a mother and/or married woman.
     */
    public const TAG_WIFE = 'WIFE';

    /**
     * @return FamilyPersonAgeInterface|null
     */
    public function getHusband(): ?FamilyPersonAgeInterface;

    /**
     * @return FamilyPersonAgeInterface|null
     */
    public function getWife(): ?FamilyPersonAgeInterface;
}
