<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualAttributeStructure\IndividualAttributeDetailInterface;

/**
 * The individual attribute structure tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface IndividualAttributeStructureInterface
{
    /**
     * A name assigned to a particular group that this person was associated with, such as a particular racial
     *  group, religious group, or a group with an inherited status.
     */
    const TAG_CAST = 'CAST';

    /**
     * The physical characteristics of a person, place, or thing.
     */
    const TAG_DSCR = 'DSCR';

    /**
     * Indicator of a level of education attained.
     */
    const TAG_EDUC = 'EDUC';

    /**
     * A number assigned to identify a person within some significant external system.
     */
    const TAG_IDNO = 'IDNO';

    /**
     * The national heritage of an individual.
     */
    const TAG_NATI = 'NATI';

    /**
     * The number of children that this person is known to be the parent of (all marriages) when subordinate
     * to an individual, or that belong to this family when subordinate to a FAM_RECORD.
     */
    const TAG_NCHI = 'NCHI';

    /**
     * The number of times this person has participated in a family as a spouse or parent.
     */
    const TAG_NMR = 'NMR';

    /**
     * The type of work or profession of an individual.
     */
    const TAG_OCCU = 'OCCU';

    /**
     * Pertaining to possessions such as real estate or other property of interest.
     */
    const TAG_PROP = 'PROP';

    /**
     * A religious denomination to which a person is affiliated or for which a record applies.
     */
    const TAG_RELI = 'RELI';

    /**
     * An address or place of residence that a family or individual resided.
     */
    const TAG_RESI = 'RESI';

    /**
     * A number assigned by the United States Social Security Administration.
     * Used for tax identification purposes.
     */
    const TAG_SSN = 'SSN';

    /**
     * The title given to or used by a person, especially of royalty or other noble class within a locality.
     */
    const TAG_TITL = 'TITL';

    /**
     * Pertaining to a noteworthy attribute or fact concerning an individual, a group, or an organization.
     * A FACT structure is usually qualified or classified by a subordinate use of the TYPE tag.
     */
    const TAG_FACT = 'FACT';

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getCasteName(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getPhysicalDescription(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getEducation(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getIdentityNumber(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getNationality(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getChildrenCount(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getMarriageCount(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getOccupation(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getProperty(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getReligion(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getResidence(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getSocialSecurityNumber(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getTitle(): array;

    /**
     * @return IndividualAttributeDetailInterface[]
     */
    public function getFact(): array;
}
