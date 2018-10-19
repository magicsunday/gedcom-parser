<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\IndividualRecordInterface;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\MultimediaLinkTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;
use MagicSunday\Gedcom\Traits\IndividualRecord\IndividualAttributeStructureTrait;
use MagicSunday\Gedcom\Traits\IndividualRecord\IndividualEventStructureTrait;
use MagicSunday\Gedcom\Traits\IndividualRecord\LdsIndividualOrdinanceTrait;

/**
 * The INDI (individual) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualRecord extends DataObject implements IndividualRecordInterface
{
    use IndividualEventStructureTrait;
    use IndividualAttributeStructureTrait;
    use LdsIndividualOrdinanceTrait;
    use ChangeDateTrait;
    use MultimediaLinkTrait;
    use NoteTrait;
    use SourceCitationTrait;

    /**
     * @inheritDoc
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_INDI);
    }

    /**
     * @inheritDoc
     */
    public function getNames(): array
    {
        return $this->getArrayValue(self::TAG_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getFamilyChild(): array
    {
        return $this->getArrayValue(self::TAG_FAMC);
    }

    /**
     * @inheritDoc
     */
    public function getFamilySpouse(): array
    {
        return $this->getArrayValue(self::TAG_FAMS);
    }

    /**
     * @inheritDoc
     */
    public function getAssociation(): array
    {
        return $this->getArrayValue(self::TAG_ASSO);
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
    public function getSex()
    {
        return $this->getValue(self::TAG_SEX);
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
    public function getAliasXref(): array
    {
        return $this->getArrayValue(self::TAG_ALIA);
    }

    /**
     * @inheritDoc
     */
    public function getAncestorInterest(): array
    {
        return $this->getArrayValue(self::TAG_ANCI);
    }

    /**
     * @inheritDoc
     */
    public function getDescendantInterest(): array
    {
        return $this->getArrayValue(self::TAG_DESI);
    }

    /**
     * @inheritDoc
     */
    public function getRecordFileNumber()
    {
        return $this->getValue(self::TAG_RFN);
    }

    /**
     * @inheritDoc
     */
    public function getAncestralFileNumber()
    {
        return $this->getValue(self::TAG_AFN);
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
