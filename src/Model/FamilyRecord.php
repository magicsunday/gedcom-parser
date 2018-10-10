<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\FamilyRecordInterface;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure;
use MagicSunday\Gedcom\Traits\Common\ChangeDate;
use MagicSunday\Gedcom\Traits\Common\MultimediaLink;
use MagicSunday\Gedcom\Traits\Common\Note;
use MagicSunday\Gedcom\Traits\Common\SourceCitation;

/**
 * The FAM (family) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyRecord extends FamilyEventStructure implements FamilyRecordInterface
{
    use ChangeDate;
    use MultimediaLink;
    use Note;
    use SourceCitation;

    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_FAM);
    }

    /**
     * @inheritDoc
     */
    public function getRestrictionNotice()
    {
        return $this->getValue(self::TAG_RESN);
    }

    /**
     * @inheritDoc
     */
    public function getHusbandXref()
    {
        return $this->getValue(self::TAG_HUSB);
    }

    /**
     * @inheritDoc
     */
    public function getWifeXref()
    {
        return $this->getValue(self::TAG_WIFE);
    }

    /**
     * @inheritDoc
     */
    public function getChildrenXref()
    {
        return $this->getValue(self::TAG_CHIL);
    }

    /**
     * @inheritDoc
     */
    public function getChildrenCount()
    {
        return $this->getValue(self::TAG_NCHI);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitterXref()
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * @inheritDoc
     */
    public function getSealingSpouse()
    {
        return $this->getValue(self::TAG_SLGS);
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
