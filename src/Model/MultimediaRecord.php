<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\MultimediaRecordInterface;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;

/**
 * The OBJE (multimedia object) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class MultimediaRecord extends DataObject implements MultimediaRecordInterface
{
    use ChangeDateTrait;
    use NoteTrait;
    use SourceCitationTrait;

    /**
     * @inheritDoc
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_OBJE);
    }

    /**
     * @inheritDoc
     */
    public function getFile(): array
    {
        return $this->getArrayValue(self::TAG_FILE);
    }

    /**
     * @inheritDoc
     */
    public function getReferenceNumber(): array
    {
        return $this->getArrayValue(self::TAG_REFN);
    }

    /**
     * @inheritDoc
     */
    public function getRecordIdNumber()
    {
        return $this->getValue(self::TAG_RIN);
    }
}
