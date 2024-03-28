<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_EVEN = 'EVEN';

    /**
     * The organization, institution, corporation, person, or another entity that has responsibility for the
     * associated context. For example, an employer of a person of an associated occupation, or a church
     * that administered rites or events, or an organization responsible for creating and/or archiving records.
     */
    public const TAG_AGNC = 'AGNC';

    /**
     * @return EventInterface[]
     */
    public function getEvents(): array;

    /**
     * @return string|null
     */
    public function getAgency(): ?string;
}
