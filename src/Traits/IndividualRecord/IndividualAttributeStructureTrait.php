<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
trait IndividualAttributeStructureTrait
{
    /**
     * @param string $key
     *
     * @return array
     */
    abstract public function getArrayValue(string $key): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getCasteName(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_CAST);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getPhysicalDescription(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_DSCR);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getEducation(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_EDUC);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getIdentityNumber(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_IDNO);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getNationality(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_NATI);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getChildrenCount(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_NCHI);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getMarriageCount(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_NMR);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getOccupation(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_OCCU);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getProperty(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_PROP);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getReligion(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_RELI);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getResidence(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_RESI);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getSocialSecurityNumber(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_SSN);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getTitle(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_TITL);
    }

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getFact(): array
    {
        return $this->getArrayValue(IndividualAttributeStructureInterface::TAG_FACT);
    }
}
