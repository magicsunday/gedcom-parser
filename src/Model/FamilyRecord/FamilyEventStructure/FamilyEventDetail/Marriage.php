<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
     * {@inheritDoc}
     */
    public function getFlag(): ?string
    {
        return $this->getValue(self::TAG_FLAG);
    }
}
