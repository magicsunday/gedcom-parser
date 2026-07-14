<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

use MagicSunday\Gedcom\ValueObject\DateValue;

/**
 * The event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
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

    public function getType(): ?string;

    public function getDate(): ?string;

    /**
     * Returns the event date as a typed value object, or NULL when no DATE is present.
     */
    public function getDateValue(): ?DateValue;

    public function getPlace(): ?PlaceStructureInterface;

    public function getAgency(): ?string;

    public function getReligion(): ?string;

    public function getCause(): ?string;

    public function getRestrictionNotice(): ?string;
}
