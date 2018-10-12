<?php
/**
 * See LICENSE.md file for further details.
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
interface EventDetailInterface
    extends AddressStructureInterface, MultimediaLinkInterface, NoteInterface, SourceCitationInterface
{
    /**
     * A descriptive word or phrase used to further classify the parent event or attribute tag.
     */
    const TAG_TYPE = 'TYPE';

    /**
     * The date of the event.
     */
    const TAG_DATE = 'DATE';

    /**
     * The place of the event.
     */
    const TAG_PLAC = 'PLAC';

    /**
     * The organization, institution, corporation, person, or other entity that has responsibility for the
     * associated context.
     */
    const TAG_AGNC = 'AGNC';

    /**
     * A name of the religion with which this person, event, or record was affiliated.
     */
    const TAG_RELI = 'RELI';

    /**
     * Used in special cases to record the reasons which precipitated an event. Normally this will be used
     * subordinate to a death event to show cause of death, such as might be listed on a death certificate.
     */
    const TAG_CAUS = 'CAUS';

    /**
     * The restriction notice is defined for Ancestral File usage.
     *
     * - confidential
     * - locked
     * - privacy
     */
    const TAG_RESN = 'RESN';

    /**
     * @return null|string
     */
    public function getType();

    /**
     * @return null|string
     */
    public function getDate();

    /**
     * @return null|PlaceStructureInterface
     */
    public function getPlace();

    /**
     * @return null|string
     */
    public function getAgency();

    /**
     * @return null|string
     */
    public function getReligion();

    /**
     * @return null|string
     */
    public function getCause();

    /**
     * @return null|string
     */
    public function getRestrictionNotice();
}
