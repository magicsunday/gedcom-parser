<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\SourceRecord\Data;

/**
 * The SOUR (source), DATA (data), EVEN (event) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface EventInterface
{
    /**
     * An enumeration of the different kinds of events that were recorded in a particular source. Each
     * enumeration is separated by a comma. Such as a parish register of births, deaths, and marriages would
     * be BIRT, DEAT, MARR.
     */
    const TAG_EVENTS_RECORDED = 'EVENTS_RECORDED';

    /**
     * A date period.
     */
    const TAG_DATE = 'DATE';

    /**
     * The name of the lowest jurisdiction that encompasses all lower-level places named in this source. For
     * example, "Oneida, Idaho" would be used as a source jurisdiction place for events occurring in the
     * various towns within Oneida County. "Idaho" would be the source jurisdiction place if the events
     * recorded took place in other counties as well as Oneida County.
     */
    const TAG_PLAC = 'PLAC';

    /**
     * @return null|string
     */
    public function getEventsRecorded();

    /**
     * @return null|string
     */
    public function getDatePeriod();

    /**
     * @return null|string
     */
    public function getPlaceName();
}
