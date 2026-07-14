<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\FamilyRecord;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetail\MarriageInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetailInterface;

/**
 * The family event structure tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface FamilyEventStructureInterface
{
    /**
     * Declaring a marriage void from the beginning (never existed).
     */
    public const TAG_ANUL = 'ANUL';

    /**
     * The event of the periodic count of the population for a designated locality, such as a national
     * or state Census.
     */
    public const TAG_CENS = 'CENS';

    /**
     * An event of dissolving a marriage through civil action.
     */
    public const TAG_DIV = 'DIV';

    /**
     * An event of filing for a divorce by a spouse.
     */
    public const TAG_DIVF = 'DIVF';

    /**
     * An event of recording or announcing an agreement between two people to become married.
     */
    public const TAG_ENGA = 'ENGA';

    /**
     * An event of an official public notice given that two people intend to marry.
     */
    public const TAG_MARB = 'MARB';

    /**
     * An event of recording a formal agreement of marriage, including the prenuptial agreement in which
     * marriage partners reach agreement about the property rights of one or both, securing property to their
     * children.
     */
    public const TAG_MARC = 'MARC';

    /**
     * A legal, common-law, or customary event of creating a family unit of a man and a woman as husband
     * and wife.
     */
    public const TAG_MARR = 'MARR';

    /**
     * An event of obtaining a legal license to marry.
     */
    public const TAG_MARL = 'MARL';

    /**
     * An event of creating an agreement between two people contemplating marriage, at which time they
     * agree to release or modify property rights that would otherwise arise from the marriage.
     */
    public const TAG_MARS = 'MARS';

    /**
     * An address or place of residence that a family or individual resided.
     */
    public const TAG_RESI = 'RESI';

    /**
     * Pertaining to a noteworthy happening related to an individual, a group, or an organization. An EVENt
     * structure is usually qualified or classified by a subordinate use of the TYPE tag.
     */
    public const TAG_EVEN = 'EVEN';

    public function getAnnulment(): ?FamilyEventDetailInterface;

    public function getCensus(): ?FamilyEventDetailInterface;

    public function getDivorce(): ?FamilyEventDetailInterface;

    public function getDivorceFiled(): ?FamilyEventDetailInterface;

    public function getEngagement(): ?FamilyEventDetailInterface;

    public function getMarriageBann(): ?FamilyEventDetailInterface;

    public function getMarriageContract(): ?FamilyEventDetailInterface;

    public function getMarriageLicense(): ?FamilyEventDetailInterface;

    public function getMarriage(): ?MarriageInterface;

    public function getMarriageSettlement(): ?FamilyEventDetailInterface;

    public function getResidence(): ?FamilyEventDetailInterface;

    public function getCustomEvent(): ?FamilyEventDetailInterface;
}
