<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructureInterface;
use MagicSunday\Gedcom\Interfaces\IndividualInterface;
use MagicSunday\Gedcom\Traits\ChangeDate;
use MagicSunday\Gedcom\Traits\MultimediaLink;
use MagicSunday\Gedcom\Traits\NoteStructure;
use MagicSunday\Gedcom\Traits\SourceCitation;

/**
 * The individual model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualRecord extends DataObject
    implements IndividualInterface
//        ChangeDateInterface,
//        NoteStructureInterface
//        PersonalNameStructureInterface
//        IndividualEventStructureInterface,
//        IndividualAttributeStructureInterface,
//        LdsIndividualOrdinanceInterface,
//        NoteStructureInterface,
//        SourceCitationInterface,
//        MultimediaLinkInterface
{
//    use \MagicSunday\Gedcom\Traits\Individual\PersonalNameStructure;

    use ChangeDate;
    use MultimediaLink;
    use NoteStructure;
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
        // TODO: Implement getRestrictionNotice() method.
    }

    /**
     * @inheritDoc
     */
    public function getSex()
    {
        // TODO: Implement getSex() method.
    }

    /**
     * @inheritDoc
     */
    public function getSubmitterXref()
    {
        // TODO: Implement getSubmitterXref() method.
    }

    /**
     * @inheritDoc
     */
    public function getAliasXref()
    {
        // TODO: Implement getAliasXref() method.
    }

    /**
     * @inheritDoc
     */
    public function getAncestorInterest()
    {
        // TODO: Implement getAncestorInterest() method.
    }

    /**
     * @inheritDoc
     */
    public function getDescendantInterest()
    {
        // TODO: Implement getDescendantInterest() method.
    }

    /**
     * @inheritDoc
     */
    public function getRecordFileNumber()
    {
        // TODO: Implement getRecordFileNumber() method.
    }

    /**
     * @inheritDoc
     */
    public function getAncestralFileNumber()
    {
        // TODO: Implement getAncestralFileNumber() method.
    }

    /**
     * @inheritDoc
     */
    public function getRecordIdNumber()
    {
        // TODO: Implement getRecordIdNumber() method.
    }

    /**
     * @return null|PersonalNameStructureInterface[]
     */
    public function getNames()
    {
        return $this->getValue(self::TAG_NAME);
    }
}
