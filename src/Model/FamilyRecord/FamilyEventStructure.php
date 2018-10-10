<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord;

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
     * @inheritDoc
     */
    public function getAnnulment()
    {
        return $this->getValue(self::TAG_ANUL);
    }

    /**
     * @inheritDoc
     */
    public function getCensus()
    {
        return $this->getValue(self::TAG_CENS);
    }

    /**
     * @inheritDoc
     */
    public function getDivorce()
    {
        return $this->getValue(self::TAG_DIV);
    }

    /**
     * @inheritDoc
     */
    public function getDivorceFiled()
    {
        return $this->getValue(self::TAG_DIVF);
    }

    /**
     * @inheritDoc
     */
    public function getEngagement()
    {
        return $this->getValue(self::TAG_ENGA);
    }

    /**
     * @inheritDoc
     */
    public function getMarriageBann()
    {
        return $this->getValue(self::TAG_MARB);
    }

    /**
     * @inheritDoc
     */
    public function getMarriageContract()
    {
        return $this->getValue(self::TAG_MARC);
    }

    /**
     * @inheritDoc
     */
    public function getMarriageLicense()
    {
        return $this->getValue(self::TAG_MARL);
    }

    /**
     * @inheritDoc
     */
    public function getMarriage()
    {
        return $this->getValue(self::TAG_MARR);
    }

    /**
     * @inheritDoc
     */
    public function getMarriageSettlement()
    {
        return $this->getValue(self::TAG_MARS);
    }

    /**
     * @inheritDoc
     */
    public function getResidence()
    {
        return $this->getValue(self::TAG_RESI);
    }

    /**
     * @inheritDoc
     */
    public function getCustomEvent()
    {
        return $this->getValue(self::TAG_EVEN);
    }
}
