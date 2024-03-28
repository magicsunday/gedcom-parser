<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

/**
 * The event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface EventDetailInterface extends AddressStructureInterface, MultimediaLinkInterface, NoteInterface, SourceCitationInterface
{
    /**
     * A descriptive word or phrase used to further classify the parent event or attribute tag.
     */
    public const TAG_TYPE = 'TYPE';

    /**
     * The date of the event.
     */
    public const TAG_DATE = 'DATE';

    /**
     * The place of the event.
     */
    public const TAG_PLAC = 'PLAC';

    /**
     * The organization, institution, corporation, person, or another entity that has responsibility for the
     * associated context.
     */
    public const TAG_AGNC = 'AGNC';

    /**
     * A name of the religion with which this person, event, or record was affiliated.
     */
    public const TAG_RELI = 'RELI';

    /**
     * Used in special cases to record the reasons which precipitated an event. Normally, this will be used
     * subordinate to a death event to show cause of death, such as might be listed on a death certificate.
     */
    public const TAG_CAUS = 'CAUS';

    /**
     * The restriction notice is defined for Ancestral File usage.
     *
     * - confidential
     * - locked
     * - privacy
     */
    public const TAG_RESN = 'RESN';

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @return PlaceStructureInterface|null
     */
    public function getPlace(): ?PlaceStructureInterface;

    /**
     * @return string|null
     */
    public function getAgency(): ?string;

    /**
     * @return string|null
     */
    public function getReligion(): ?string;

    /**
     * @return string|null
     */
    public function getCause(): ?string;

    /**
     * @return string|null
     */
    public function getRestrictionNotice(): ?string;
}
