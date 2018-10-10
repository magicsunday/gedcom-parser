<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\FamilyRecord;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetailInterface;

/**
 * The family event structure tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface FamilyEventStructureInterface
{
    /**
     * Declaring a marriage void from the beginning (never existed).
     */
    const TAG_ANUL = 'ANUL';

    /**
     * The event of the periodic count of the population for a designated locality, such as a national
     * or state Census.
     */
    const TAG_CENS = 'CENS';

    /**
     * An event of dissolving a marriage through civil action.
     */
    const TAG_DIV = 'DIV';

    /**
     * An event of filing for a divorce by a spouse.
     */
    const TAG_DIVF = 'DIVF';

    /**
     * An event of recording or announcing an agreement between two people to become married.
     */
    const TAG_ENGA = 'ENGA';

    /**
     * An event of an official public notice given that two people intend to marry.
     */
    const TAG_MARB = 'MARB';

    /**
     * An event of recording a formal agreement of marriage, including the prenuptial agreement in which
     * marriage partners reach agreement about the property rights of one or both, securing property to their
     * children.
     */
    const TAG_MARC = 'MARC';

    /**
     * A legal, common-law, or customary event of creating a family unit of a man and a woman as husband
     * and wife.
     */
    const TAG_MARR = 'MARR';

    /**
     * An event of obtaining a legal license to marry.
     */
    const TAG_MARL = 'MARL';

    /**
     * An event of creating an agreement between two people contemplating marriage, at which time they
     * agree to release or modify property rights that would otherwise arise from the marriage.
     */
    const TAG_MARS = 'MARS';

    /**
     * An address or place of residence that a family or individual resided.
     */
    const TAG_RESI = 'RESI';

    /**
     * Pertaining to a noteworthy happening related to an individual, a group, or an organization. An EVENt
     * structure is usually qualified or classified by a subordinate use of the TYPE tag.
     */
    const TAG_EVEN = 'EVEN';

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getAnnulment();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getCensus();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getDivorce();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getDivorceFiled();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getEngagement();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getMarriageBann();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getMarriageContract();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getMarriageLicense();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getMarriage();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getMarriageSettlement();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getResidence();

    /**
     * @return null|FamilyEventDetailInterface
     */
    public function getCustomEvent();
}
