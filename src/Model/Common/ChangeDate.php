<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Model\Note;

/**
 * The change date is intended to only record the last change to a record. Some systems may want to
 * manage the change process with more detail, but it is sufficient for GEDCOM purposes to indicate
 * the last time that a record was modified.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ChangeDate extends DataObject
{
    /**
     * The date that this data was changed.
     */
    const TAG_DATE = 'DATE';

    /**
     * A list of assigned notes.
     */
    const TAG_NOTE = 'NOTE';

    /**
     * @return DateExact
     */
    public function getDate(): DateExact
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * @return Note[]
     */
    public function getNotes(): array
    {
        return $this->getValue(self::TAG_NOTE);
    }
}
