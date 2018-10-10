<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\IndividualRecordInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure;
use MagicSunday\Gedcom\Traits\Common\ChangeDate;
use MagicSunday\Gedcom\Traits\Common\MultimediaLink;
use MagicSunday\Gedcom\Traits\Common\Note;
use MagicSunday\Gedcom\Traits\Common\SourceCitation;


/**
 * The INDI (individual) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualRecord extends IndividualEventStructure implements IndividualRecordInterface
{
    use ChangeDate;
    use MultimediaLink;
    use Note;
    use SourceCitation;

    /**
     * Returns the XREF.
     *
     * @return null|string
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_INDI);
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
    public function getSubmitterXref()
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * @inheritDoc
     */
    public function getAliasXref()
    {
        return $this->getValue(self::TAG_ALIA);
    }

    /**
     * @inheritDoc
     */
    public function getAncestorInterest()
    {
        return $this->getValue(self::TAG_ANCI);
    }

    /**
     * @inheritDoc
     */
    public function getDescendantInterest()
    {
        return $this->getValue(self::TAG_DESI);
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
