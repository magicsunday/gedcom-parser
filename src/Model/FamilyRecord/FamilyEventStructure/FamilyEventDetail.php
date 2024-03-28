<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetailInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyPersonAgeInterface;
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
     * {@inheritDoc}
     */
    public function getHusband(): ?FamilyPersonAgeInterface
    {
        return $this->getValue(self::TAG_HUSB);
    }

    /**
     * {@inheritDoc}
     */
    public function getWife(): ?FamilyPersonAgeInterface
    {
        return $this->getValue(self::TAG_WIFE);
    }
}
