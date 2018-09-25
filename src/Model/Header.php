<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Model;

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
     * @var Source
     */
    private $source;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var string
     */
    private $submitter;

    /**
     * @var string
     */
    private $submission;

    /**
     * @var string
     */
    private $file;

    /**
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
     * Information about the use of GEDCOM in a transmission.
     *
     * @var CharacterSet
     */
    private $characterSet;

    /**
     * @var string
     */
    private $language;

    /**
     * @var Place
     */
    private $place;

    /**
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
     * @return Date
     */
    public function getDate(): Date
    {
        return $this->date;
    }

    /**
     * @param Date $date
     *
     * @return self
     */
    public function setDate(Date $date): self
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
