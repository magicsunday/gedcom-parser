<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Individual\NameInterface;

/**
 * The individual tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface IndividualInterface
    extends
        NameInterface,
        ChangeDateInterface,
        NoteStructureInterface,
        SourceCitationInterface,
        MultimediaLinkInterface

//        IndividualEventStructureInterface,
//        IndividualAttributeStructureInterface,
//        LdsIndividualOrdinanceInterface,
{
    /**
     * The identifier.
     */
    const TAG_XREF_INDI = 'XREF:INDI';

    /**
     * The restriction notice is defined for Ancestral File usage. Ancestral File download GEDCOM files
     * may contain this data.
     *
     * Either "confidential", "locked" or "privacy"
     */
    const TAG_RESN = 'RESN';

    /**
     * A code that indicates the sex of the individual:
     *
     * M = Male
     * F = Female
     * U = Undetermined from available records and quite sure that it can’t be
     */
    const TAG_SEX  = 'SEX';

    /**
     * A submitter XREF pointer.
     */
    const TAG_SUBM = 'SUBM';

    /**
     * An indicator to link different record descriptions of a person who may be the same person.
     */
    const TAG_ALIA = 'ALIA';

    /**
     * Indicates an interest in additional research for ancestors of this individual.
     */
    const TAG_ANCI = 'ANCI';

    /**
     * Indicates an interest in research to identify additional descendants of this individual.
     */
    const TAG_DESI = 'DESI';

    /**
     * A permanent number assigned to a record that uniquely identifies it within a known file.
     */
    const TAG_RFN = 'RFN';

    /**
     * A unique permanent record file number of an individual record stored in Ancestral File.
     */
    const TAG_AFN = 'AFN';

    /**
     * A number assigned to a record by an originating automated system that can be used by a receiving
     * system to report results pertaining to that record.
     */
    const TAG_RIN = 'RIN';

    /**
     * Returns the XREF.
     *
     * @return null|string
     */
    public function getXref();

    /**
     * @return null|string
     */
    public function getRestrictionNotice();

    /**
     * @return null|string
     */
    public function getSex();

    /**
     * @return null|string
     */
    public function getSubmitterXref();

    /**
     * @return null|string
     */
    public function getAliasXref();

    /**
     * @return null|string
     */
    public function getAncestorInterest();

    /**
     * @return null|string
     */
    public function getDescendantInterest();

    /**
     * @return null|string
     */
    public function getRecordFileNumber();

    /**
     * @return null|string
     */
    public function getAncestralFileNumber();

    /**
     * @return null|string
     */
    public function getRecordIdNumber();
}