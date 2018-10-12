<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\SourceRecordInterface;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\MultimediaLinkTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The SOUR (source) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceRecord extends DataObject implements SourceRecordInterface
{
    use ChangeDateTrait;
    use MultimediaLinkTrait;
    use NoteTrait;

    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_SOUR);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->getValue(self::TAG_DATA);
    }

    /**
     * @inheritDoc
     */
    public function getAuthor()
    {
        return $this->getValue(self::TAG_AUTH);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getValue(self::TAG_TITL);
    }

    /**
     * @inheritDoc
     */
    public function getAbbreviation()
    {
        return $this->getValue(self::TAG_ABBR);
    }


    /**
     * @inheritDoc
     */
    public function getPublication()
    {
        return $this->getValue(self::TAG_PUBL);
    }

    /**
     * @inheritDoc
     */
    public function getText()
    {
        return $this->getValue(self::TAG_TEXT);
    }

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->getValue(self::TAG_REPO);
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
