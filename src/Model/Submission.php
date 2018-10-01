<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Common\ChangeDate;

/**
 * The sending system uses a submission record to send instructions and information to the receiving
 * system. TempleReady processes submission records to determine which temple the cleared records
 * should be directed to. The submission record is also used for communication between Ancestral File
 * download requests and TempleReady. Each GEDCOM transmission file should have only one
 * submission record. Multiple submissions are handled by creating separate GEDCOM transmission files.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Submission extends DataObject //implements NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a SUBmissioN record.
     */
    const TAG_XREF_SUBN = 'XREF:SUBN';

    /**
     * A pointer to, or a cross-reference identifier of, a SUBMitter record.
     */
    const TAG_SUBM = 'SUBM';

    /**
     * Name under which family names for ordinances are stored in the temple's family file.
     */
    const TAG_FAMF = 'FAMF';

    /**
     * An abbreviation of the temple in which LDS temple ordinances were performed.
     */
    const TAG_TEMP = 'TEMP';

    /**
     * The number of generations of ancestors included in this transmission. This value is usually provided
     * when FamilySearch programs build a GEDCOM file for a patron requesting a download of ancestors.
     */
    const TAG_ANCE = 'ANCE';

    /**
     * The number of generations of descendants included in this transmission. This value is usually provided
     * when FamilySearch programs build a GEDCOM file for a patron requesting a download of descendants.
     */
    const TAG_DESC = 'DESC';

    /**
     * A flag that indicates whether submission should be processed for clearing temple ordinances.
     */
    const TAG_ORDI = 'ORDI';

    /**
     * A unique record identification number assigned to the record by the source system. This number is
     * intended to serve as a more sure means of identification of a record for reconciling differences in data
     * between two interfacing systems.
     */
    const TAG_RIN  = 'RIN';

    /**
     * A list of assigned notes.
     */
    const TAG_NOTE = 'NOTE';

    /**
     * The change date is intended to only record the last change to a record. Some systems may want to
     * manage the change process with more detail, but it is sufficient for GEDCOM purposes to indicate
     * the last time that a record was modified.
     */
    const TAG_CHAN = 'CHAN';

    /**
     * @return null|string
     */
    public function getSubmissionXref()
    {
        return $this->getValue(self::TAG_XREF_SUBN);
    }

    /**
     * @return null|string
     */
    public function getSubmitterXref()
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * @return null|string
     */
    public function getFamilyFile()
    {
        return $this->getValue(self::TAG_FAMF);
    }

    /**
     * @return null|string
     */
    public function getTempleCode()
    {
        return $this->getValue(self::TAG_TEMP);
    }

    /**
     * @return null|string
     */
    public function getAncestorGenerations()
    {
        return $this->getValue(self::TAG_ANCE);
    }

    /**
     * @return null|string
     */
    public function getDescendantGenerations()
    {
        return $this->getValue(self::TAG_DESC);
    }

    /**
     * @return null|string
     */
    public function getOrdinanceFlag()
    {
        return $this->getValue(self::TAG_ORDI);
    }

    /**
     * @return null|string
     */
    public function getRecordIdentificationNumber()
    {
        return $this->getValue(self::TAG_RIN);
    }

    /**
     * @return null|array
     */
    public function getNotes()
    {
        return $this->getValue(self::TAG_NOTE);
    }

    /**
     * @return null|ChangeDate
     */
    public function getChangeDate()
    {
        return $this->getValue(self::TAG_CHAN);
    }
}
