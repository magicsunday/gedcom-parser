<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_FLAG = 'EVENT_FLAG';

    /**
     * @return string|null
     */
    public function getFlag(): ?string;
}
