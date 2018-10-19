<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\SourceRecord;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\SourceRecord\Data\EventInterface;

/**
 * The SOUR (source), DATA (data) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface DataInterface extends NoteInterface
{
    /**
     * The event structure.
     */
    const TAG_EVEN = 'EVEN';

    /**
     * The organization, institution, corporation, person, or other entity that has responsibility for the
     * associated context. For example, an employer of a person of an associated occupation, or a church
     * that administered rites or events, or an organization responsible for creating and/or archiving records.
     */
    const TAG_AGNC = 'AGNC';

    /**
     * @return EventInterface[]
     */
    public function getEvents(): array;

    /**
     * @return null|string
     */
    public function getAgency();
}
