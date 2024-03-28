<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetail\MarriageInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetailInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The family event structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyEventStructure extends DataObject implements FamilyEventStructureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAnnulment(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_ANUL);
    }

    /**
     * {@inheritDoc}
     */
    public function getCensus(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_CENS);
    }

    /**
     * {@inheritDoc}
     */
    public function getDivorce(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_DIV);
    }

    /**
     * {@inheritDoc}
     */
    public function getDivorceFiled(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_DIVF);
    }

    /**
     * {@inheritDoc}
     */
    public function getEngagement(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_ENGA);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarriageBann(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_MARB);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarriageContract(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_MARC);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarriageLicense(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_MARL);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarriage(): ?MarriageInterface
    {
        return $this->getValue(self::TAG_MARR);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarriageSettlement(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_MARS);
    }

    /**
     * {@inheritDoc}
     */
    public function getResidence(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_RESI);
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomEvent(): ?FamilyEventDetailInterface
    {
        return $this->getValue(self::TAG_EVEN);
    }
}
