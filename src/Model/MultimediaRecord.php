<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\MultimediaRecordInterface;
use MagicSunday\Gedcom\Traits\Common\ChangeDate;
use MagicSunday\Gedcom\Traits\Common\Note;
use MagicSunday\Gedcom\Traits\Common\SourceCitation;

/**
 * The OBJE (multimedia object) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class MultimediaRecord extends DataObject implements MultimediaRecordInterface
{
    use ChangeDate;
    use Note;
    use SourceCitation;

    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_OBJE);
    }

    /**
     * @inheritDoc
     */
    public function getFile()
    {
        return $this->getValue(self::TAG_FILE);
    }

    /**
     * @inheritDoc
     */
    public function getReferenceNumber()
    {
        return $this->getValue(self::TAG_REFN);
    }

    /**
     * @inheritDoc
     */
    public function getRecordIdNumber()
    {
        return $this->getValue(self::TAG_RIN);
    }
}
