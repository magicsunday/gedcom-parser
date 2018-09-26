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
class Submission implements NoteInterface
{
    /**
     * @var string
     */
    private $submitter;

    /**
     * Name under which family names for ordinances are stored in the temple's family file.
     *
     * @var string
     */
    private $familyFile;

    /**
     * An abbreviation of the temple in which LDS temple ordinances were performed.
     *
     * @var string
     */
    private $templeCode;

    /**
     * The number of generations of ancestors included in this transmission. This value is usually provided
     * when FamilySearch programs build a GEDCOM file for a patron requesting a download of ancestors.
     *
     * @var string
     */
    private $ancestorGenerations;

    /**
     * The number of generations of descendants included in this transmission. This value is usually provided
     * when FamilySearch programs build a GEDCOM file for a patron requesting a download of descendants.
     *
     * @var string
     */
    private $descendantGenerations;

    /**
     * A flag that indicates whether submission should be processed for clearing temple ordinances.
     *
     * @var bool
     */
    private $ordinanceFlag;

    /**
     * A unique record identification number assigned to the record by the source system. This number is
     * intended to serve as a more sure means of identification of a record for reconciling differences in data
     * between two interfacing systems.
     *
     * @var string
     */
    private $recordIdentificationNumber;

    /**
     * A list of assigned notes.
     *
     * @var array
     */
    private $notes = [];

    /**
     * The change date is intended to only record the last change to a record. Some systems may want to
     * manage the change process with more detail, but it is sufficient for GEDCOM purposes to indicate
     * the last time that a record was modified.
     *
     * @var ChangeDate
     */
    private $changeDate;

    /**
     * @return string
     */
    public function getSubmitter(): string
    {
        return $this->submitter;
    }

    /**
     * @param string $submitter
     *
     * @return self
     */
    public function setSubmitter(string $submitter): self
    {
        $this->submitter = $submitter;
        return $this;
    }

    /**
     * @return string
     */
    public function getFamilyFile(): string
    {
        return $this->familyFile;
    }

    /**
     * @param string $familyFile
     *
     * @return self
     */
    public function setFamilyFile(string $familyFile): self
    {
        $this->familyFile = $familyFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getTempleCode(): string
    {
        return $this->templeCode;
    }

    /**
     * @param string $templeCode
     *
     * @return self
     */
    public function setTempleCode(string $templeCode): self
    {
        $this->templeCode = $templeCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAncestorGenerations(): string
    {
        return $this->ancestorGenerations;
    }

    /**
     * @param string $ancestorGenerations
     *
     * @return self
     */
    public function setAncestorGenerations(string $ancestorGenerations): self
    {
        $this->ancestorGenerations = $ancestorGenerations;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescendantGenerations(): string
    {
        return $this->descendantGenerations;
    }

    /**
     * @param string $descendantGenerations
     *
     * @return self
     */
    public function setDescendantGenerations(string $descendantGenerations): self
    {
        $this->descendantGenerations = $descendantGenerations;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOrdinanceFlag(): bool
    {
        return $this->ordinanceFlag;
    }

    /**
     * @param bool $ordinanceFlag
     *
     * @return self
     */
    public function setOrdinanceFlag(bool $ordinanceFlag): self
    {
        $this->ordinanceFlag = $ordinanceFlag;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecordIdentificationNumber(): string
    {
        return $this->recordIdentificationNumber;
    }

    /**
     * @param string $recordIdentificationNumber
     *
     * @return self
     */
    public function setRecordIdentificationNumber(string $recordIdentificationNumber): self
    {
        $this->recordIdentificationNumber = $recordIdentificationNumber;
        return $this;
    }

    /**
     * @return array
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * @param array $notes
     *
     * @return self
     */
    public function setNotes(array $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addNote(Note $note): NoteInterface
    {
        $this->notes[] = $note;
        return $this;
    }

    /**
     * @return ChangeDate
     */
    public function getChangeDate(): ChangeDate
    {
        return $this->changeDate;
    }

    /**
     * @param ChangeDate $changeDate
     *
     * @return self
     */
    public function setChangeDate(ChangeDate $changeDate): self
    {
        $this->changeDate = $changeDate;
        return $this;
    }
}
