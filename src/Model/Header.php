<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Common\DateExact;
use MagicSunday\Gedcom\Model\Header\CharacterSet;
use MagicSunday\Gedcom\Model\Header\GedcomInfo;
use MagicSunday\Gedcom\Model\Header\Note;
use MagicSunday\Gedcom\Model\Header\Place;

/**
 * The header structure provides information about the entire transmission.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Header
{
    /**
     * The source.
     *
     * @var Source
     */
    private $source;

    /**
     * The destination system name identifies the intended receiving system.
     *
     * @var string
     */
    private $destination;

    /**
     * The date that this transmission was created.
     *
     * @var DateExact
     */
    private $date;

    /**
     * The submitter identifier. The submitter record identifies an individual or organization that contributed
     * information contained in the GEDCOM transmission.
     *
     * @var string
     */
    private $submitter;

    /**
     * The submission identifier. The sending system uses a submission record to send instructions and
     * information to the receiving system.
     *
     * @var string
     */
    private $submission;

    /**
     * The name of the GEDCOM transmission file. If the file name includes a file extension it must be
     * shown in the form (filename.ext).
     *
     * @var string
     */
    private $file;

    /**
     * A copyright statement needed to protect the copyrights of the submitter of this GEDCOM file.
     *
     * @var string
     */
    private $copyright;

    /**
     * Information about the use of GEDCOM in a transmission.
     *
     * @var GedcomInfo
     */
    private $gedcomInfo;

    /**
     * A code value that represents the character set to be used to interpret this data.
     *
     * @var CharacterSet
     */
    private $characterSet;

    /**
     * The human language in which the data in the transmission is normally read or written.
     *
     * @var string
     */
    private $language;

    /**
     * A place. This shows the jurisdictional entities that are named in a sequence from the lowest to the
     * highest jurisdiction.
     *
     * @var Place
     */
    private $place;

    /**
     * A note that a user enters to describe the contents of the lineage-linked file in terms of
     * "ancestors or descendants of" so that the person receiving the data knows what genealogical
     * information the transmission contains.
     *
     * @var Note
     */
    private $note;

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @param Source $source
     *
     * @return self
     */
    public function setSource(Source $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     *
     * @return self
     */
    public function setDestination(string $destination): self
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @return DateExact
     */
    public function getDate(): DateExact
    {
        return $this->date;
    }

    /**
     * @param DateExact $date
     *
     * @return self
     */
    public function setDate(DateExact $date): self
    {
        $this->date = $date;
        return $this;
    }

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
    public function getSubmission(): string
    {
        return $this->submission;
    }

    /**
     * @param string $submission
     *
     * @return self
     */
    public function setSubmission(string $submission): self
    {
        $this->submission = $submission;
        return $this;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     *
     * @return self
     */
    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return string
     */
    public function getCopyright(): string
    {
        return $this->copyright;
    }

    /**
     * @param string $copyright
     *
     * @return self
     */
    public function setCopyright(string $copyright): self
    {
        $this->copyright = $copyright;
        return $this;
    }

    /**
     * @return GedcomInfo
     */
    public function getGedcomInfo(): GedcomInfo
    {
        return $this->gedcomInfo;
    }

    /**
     * @param GedcomInfo $gedcomInfo
     *
     * @return self
     */
    public function setGedcomInfo(GedcomInfo $gedcomInfo): self
    {
        $this->gedcomInfo = $gedcomInfo;
        return $this;
    }

    /**
     * @return CharacterSet
     */
    public function getCharacterSet(): CharacterSet
    {
        return $this->characterSet;
    }

    /**
     * @param CharacterSet $characterSet
     *
     * @return self
     */
    public function setCharacterSet(CharacterSet $characterSet): self
    {
        $this->characterSet = $characterSet;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return self
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return Place
     */
    public function getPlace(): Place
    {
        return $this->place;
    }

    /**
     * @param Place $place
     *
     * @return self
     */
    public function setPlace(Place $place): self
    {
        $this->place = $place;
        return $this;
    }

    /**
     * @return Note
     */
    public function getNote(): Note
    {
        return $this->note;
    }

    /**
     * @param Note $note
     *
     * @return self
     */
    public function setNote(Note $note): self
    {
        $this->note = $note;
        return $this;
    }
}
