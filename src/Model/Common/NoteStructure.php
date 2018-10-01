<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Model\DataObject;

/**
 * A note structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NoteStructure extends DataObject
{
    /**
     * A pointer to, or a cross-reference identifier of, a note record.
     */
    const TAG_XREF_NOTE = 'XREF:NOTE';

    /**
     * Comments or opinions from the submitter.
     */
    const TAG_NOTE = 'NOTE';

    /**
     * @return null|string
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_NOTE);
    }

    /**
     * @return null|string
     */
    public function getNote()
    {
        return $this->getValue(self::TAG_NOTE);
    }
}
