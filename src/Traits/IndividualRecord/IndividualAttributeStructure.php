<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualAttributeStructure\IndividualAttributeDetailInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualAttributeStructureInterface;

/**
 * The individual attribute structure methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait IndividualAttributeStructure
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getValue(string $key);

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getCasteName()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_CAST);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getPhysicalDescription()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_DSCR);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getEducation()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_EDUC);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getIdentityNumber()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_IDNO);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getNationality()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_NATI);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getChildrenCount()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_NCHI);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getMarriageCount()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_NMR);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getOccupation()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_OCCU);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getProperty()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_PROP);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getReligion()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_RELI);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getResidence()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_RESI);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getSocialSecurityNumber()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_SSN);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getTitle()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_TITL);
    }

    /**
     * @return null|IndividualAttributeDetailInterface
     */
    public function getFact()
    {
        return $this->getValue(IndividualAttributeStructureInterface::TAG_FACT);
    }
}
