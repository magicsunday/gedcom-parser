<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\FamilyRecordInterface;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\MultimediaLinkTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;

/**
 * The FAM (family) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyRecord extends FamilyEventStructure implements FamilyRecordInterface
{
    use ChangeDateTrait;
    use MultimediaLinkTrait;
    use NoteTrait;
    use SourceCitationTrait;

    /**
     * @inheritDoc
     */
    public function getXref(): string
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
    public function getChildrenXref(): array
    {
        return $this->getArrayValue(self::TAG_CHIL);
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
    public function getSubmitterXref(): array
    {
        return $this->getArrayValue(self::TAG_SUBM);
    }

    /**
     * @inheritDoc
     */
    public function getSpouseSealing(): array
    {
        return $this->getArrayValue(self::TAG_SLGS);
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
