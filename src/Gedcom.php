<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\HeaderRecord;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\MultimediaRecord;
use MagicSunday\Gedcom\Model\NoteRecord;
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\SubmissionRecord;
use MagicSunday\Gedcom\Model\SubmitterRecord;

/**
 * A parsed GEDCOM file.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Gedcom
{
    /**
     * The header.
     *
     * @var HeaderRecord
     */
    private $header;

    /**
     * The submission record.
     *
     * @var SubmissionRecord
     */
    private $submission;

    /**
     * A list of families.
     *
     * @var FamilyRecord[]
     */
    private $families = [];

    /**
     * A list of individuals.
     *
     * @var IndividualRecord[]
     */
    private $individuals = [];

    /**
     * A list of medias.
     *
     * @var MultimediaRecord[]
     */
    private $medias = [];

    /**
     * A list of notes.
     *
     * @var NoteRecord[]
     */
    private $notes = [];

    /**
     * A list of repositories.
     *
     * @var RepositoryRecord[]
     */
    private $repositories = [];

    /**
     * A list of sources.
     *
     * @var SourceRecord[]
     */
    private $sources = [];

    /**
     * The submitter record.
     *
     * @var SubmitterRecord
     */
    private $submitter;

    /**
     * @return HeaderRecord
     */
    public function getHeader(): HeaderRecord
    {
        return $this->header;
    }

    /**
     * @param HeaderRecord $header
     *
     * @return self
     */
    public function setHeader(HeaderRecord $header): self
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @return null|SubmissionRecord
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @param SubmissionRecord $submission
     *
     * @return self
     */
    public function setSubmission(SubmissionRecord $submission): self
    {
        $this->submission = $submission;
        return $this;
    }

    /**
     * @return FamilyRecord[]
     */
    public function getFamilies(): array
    {
        return $this->families;
    }

    /**
     * Adds an family to the list.
     *
     * @param FamilyRecord $family
     *
     * @return self
     */
    public function addFamily(FamilyRecord $family): self
    {
        $this->families[] = $family;
        return $this;
    }

    /**
     * @return IndividualRecord[]
     */
    public function getIndividuals(): array
    {
        return $this->individuals;
    }

    /**
     * Adds an individual to the list.
     *
     * @param IndividualRecord $individual
     *
     * @return self
     */
    public function addIndividual(IndividualRecord $individual): self
    {
        $this->individuals[] = $individual;
        return $this;
    }

    /**
     * @return MultimediaRecord[]
     */
    public function getMedias(): array
    {
        return $this->medias;
    }

    /**
     * @param MultimediaRecord[] $medias
     *
     * @return self
     */
    public function setMedias(array $medias): self
    {
        $this->medias = $medias;
        return $this;
    }

    /**
     * Adds an multimedia record to the list.
     *
     * @param MultimediaRecord $media
     *
     * @return self
     */
    public function addMedia(MultimediaRecord $media): self
    {
        $this->medias[] = $media;
        return $this;
    }

    /**
     * @return NoteRecord[]
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * @param NoteRecord[] $notes
     *
     * @return self
     */
    public function setNotes(array $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Adds an note to the list.
     *
     * @param NoteRecord $note
     *
     * @return self
     */
    public function addNote(NoteRecord $note): self
    {
        $this->notes[] = $note;
        return $this;
    }

    /**
     * @return RepositoryRecord[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }

    /**
     * @param RepositoryRecord[] $repositories
     *
     * @return self
     */
    public function setRepositories(array $repositories): self
    {
        $this->repositories = $repositories;
        return $this;
    }

    /**
     * Adds an repository to the list.
     *
     * @param RepositoryRecord $repository
     *
     * @return self
     */
    public function addRepository(RepositoryRecord $repository): self
    {
        $this->repositories[] = $repository;
        return $this;
    }

    /**
     * @return SourceRecord[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @param SourceRecord[] $sources
     *
     * @return self
     */
    public function setSources(array $sources): self
    {
        $this->sources = $sources;
        return $this;
    }

    /**
     * Adds an source to the list.
     *
     * @param SourceRecord $source
     *
     * @return self
     */
    public function addSource(SourceRecord $source): self
    {
        $this->sources[] = $source;
        return $this;
    }

    /**
     * @return SubmitterRecord
     */
    public function getSubmitter(): SubmitterRecord
    {
        return $this->submitter;
    }

    /**
     * @param SubmitterRecord $submitter
     *
     * @return self
     */
    public function setSubmitter(SubmitterRecord $submitter): self
    {
        $this->submitter = $submitter;
        return $this;
    }
}
