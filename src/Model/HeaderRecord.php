<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\CharacterSetInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\GedcomInfoInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\SourceInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecordInterface;

/**
 * The HEAD (header) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class HeaderRecord extends DataObject implements HeaderRecordInterface
{
    /**
     * @inheritDoc
     */
    public function getSource(): SourceInterface
    {
        return $this->getValue(self::TAG_SOUR);
    }

    /**
     * @inheritDoc
     */
    public function getDestination()
    {
        return $this->getValue(self::TAG_DEST);
    }

    /**
     * @inheritDoc
     */
    public function getTransmissionDate()
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitter(): string
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * @inheritDoc
     */
    public function getSubmission()
    {
        return $this->getValue(self::TAG_SUBN);
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
    public function getCopyright()
    {
        return $this->getValue(self::TAG_COPR);
    }

    /**
     * @inheritDoc
     */
    public function getGedcomInfo(): GedcomInfoInterface
    {
        return $this->getValue(self::TAG_GEDC);
    }

    /**
     * @inheritDoc
     */
    public function getCharacterSet(): CharacterSetInterface
    {
        return $this->getValue(self::TAG_CHAR);
    }

    /**
     * @inheritDoc
     */
    public function getLanguage()
    {
        return $this->getValue(self::TAG_LANG);
    }

    /**
     * @inheritDoc
     */
    public function getPlace()
    {
        return $this->getValue(self::TAG_PLAC);
    }

    /**
     * @inheritDoc
     */
    public function getNote()
    {
        return $this->getValue(self::TAG_NOTE);
    }
}
