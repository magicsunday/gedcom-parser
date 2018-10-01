<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Model\Family;
use MagicSunday\Gedcom\Model\Media;
use MagicSunday\Gedcom\Model\Header;
use MagicSunday\Gedcom\Model\Individual;
use MagicSunday\Gedcom\Model\MultimediaLink;
use MagicSunday\Gedcom\Model\NoteRecord;
use MagicSunday\Gedcom\Model\Submission;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\Repository;
use MagicSunday\Gedcom\Model\Source;
use MagicSunday\Gedcom\Model\Submitter;

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
     * @var Header
     */
    private $header;

    /**
     * The submission record.
     *
     * @var Submission
     */
    private $submission;

    /**
     * A list of families.
     *
     * @var Family[]
     */
    private $families = [];

    /**
     * A list of individuals.
     *
     * @var Individual[]
     */
    private $individuals = [];

    /**
     * A list of medias.
     *
     * @var MultimediaLink[]
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
     * @var Repository[]
     */
    private $repositories = [];

    /**
     * A list of sources.
     *
     * @var Source[]
     */
    private $sources = [];

    /**
     * The submitter record.
     *
     * @var Submitter
     */
    private $submitter;

    /**
     * @return Header
     */
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * @param Header $header
     *
     * @return self
     */
    public function setHeader(Header $header): self
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @return null|Submission
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @param Submission $submission
     *
     * @return self
     */
    public function setSubmission(Submission $submission): self
    {
        $this->submission = $submission;
        return $this;
    }

    /**
     * @return Family[]
     */
    public function getFamilies(): array
    {
        return $this->families;
    }

    /**
     * Adds an family to the list.
     *
     * @param Family $family
     *
     * @return self
     */
    public function addFamily(Family $family): self
    {
        $this->families[] = $family;
        return $this;
    }

    /**
     * @return Individual[]
     */
    public function getIndividuals(): array
    {
        return $this->individuals;
    }

    /**
     * Adds an individual to the list.
     *
     * @param Individual $individual
     *
     * @return self
     */
    public function addIndividual(Individual $individual): self
    {
        $this->individuals[] = $individual;
        return $this;
    }

    /**
     * @return Media[]
     */
    public function getMedias(): array
    {
        return $this->medias;
    }

    /**
     * @param Media[] $medias
     *
     * @return self
     */
    public function setMedias(array $medias): self
    {
        $this->medias = $medias;
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
     * @return Repository[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }

    /**
     * @param Repository[] $repositories
     *
     * @return self
     */
    public function setRepositories(array $repositories): self
    {
        $this->repositories = $repositories;
        return $this;
    }

    /**
     * @return Source[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @param Source[] $sources
     *
     * @return self
     */
    public function setSources(array $sources): self
    {
        $this->sources = $sources;
        return $this;
    }

    /**
     * @return Submitter
     */
    public function getSubmitter(): Submitter
    {
        return $this->submitter;
    }

    /**
     * @param Submitter $submitter
     *
     * @return self
     */
    public function setSubmitter(Submitter $submitter): self
    {
        $this->submitter = $submitter;
        return $this;
    }
}
