<?php
declare(strict_types = 1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Model\Individual;

use MagicSunday\Gedcom\Model\ArrayInterface;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\NoteInterface;
use MagicSunday\Gedcom\Model\Source;
use MagicSunday\Gedcom\Model\SourceInterface;

/**
 * The name pieces model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NamePieces implements NoteInterface, SourceInterface, ArrayInterface
{
    /**
     * Text which appears on a name line before the given and surname parts of a name.
     *   i.e. (Lt. Cmndr.) Joseph /Allen/ jr.
     *
     * In this example Lt. Cmndr. is considered as the name prefix portion.
     *
     * @var string
     */
    private $namePrefix;

    /**
     * A given or earned name used for official identification of a person.
     *
     * @var string
     */
    private $givenName;

    /**
     * A descriptive or familiar that is used instead of, or in addition to, one's proper name.
     *
     * @var string
     */
    private $nickname;

    /**
     * A name piece used as a non-indexing pre-part of a surname.
     *
     * @var string
     */
    private $surnamePrefix;

    /**
     * A family name passed on or used by members of a family.
     *
     * @var string
     */
    private $surname;

    /**
     * Text which appears on a name line after or behind the given and surname parts of a name.
     *   i.e. Lt. Cmndr. Joseph /Allen/ (jr.)
     *
     * In this example jr. is considered as the name suffix portion.
     *
     * @var string
     */
    private $nameSuffix;

    /**
     * A list of notes assigned to the name.
     *
     * @var array
     */
    private $notes = [];

    /**
     * A list of sources assigned to the name.
     *
     * @var array
     */
    private $sources = [];

    /**
     * @return string
     */
    public function getNamePrefix(): string
    {
        return $this->namePrefix;
    }

    /**
     * @param string $namePrefix
     *
     * @return self
     */
    public function setNamePrefix(string $namePrefix): self
    {
        $this->namePrefix = $namePrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getGivenName(): string
    {
        return $this->givenName;
    }

    /**
     * @param string $givenName
     *
     * @return self
     */
    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNickname(): string
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     *
     * @return self
     */
    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurnamePrefix(): string
    {
        return $this->surnamePrefix;
    }

    /**
     * @param string $surnamePrefix
     *
     * @return self
     */
    public function setSurnamePrefix(string $surnamePrefix): self
    {
        $this->surnamePrefix = $surnamePrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return self
     */
    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string
     */
    public function getNameSuffix(): string
    {
        return $this->nameSuffix;
    }

    /**
     * @param string $nameSuffix
     *
     * @return self
     */
    public function setNameSuffix(string $nameSuffix): self
    {
        $this->nameSuffix = $nameSuffix;
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
     * @return array
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @param array $sources
     *
     * @return self
     */
    public function setSources(array $sources): self
    {
        $this->sources = $sources;
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
     * {@inheritdoc}
     */
    public function addSource(Source $source): SourceInterface
    {
        $this->sources[] = $source;
        return $this;
    }

    /**
     * Returns the object as array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'namePrefix'    => $this->namePrefix,
            'givenName'     => $this->givenName,
            'nickname'      => $this->nickname,
            'surnamePrefix' => $this->surnamePrefix,
            'surname'       => $this->surname,
            'nameSuffix'    => $this->nameSuffix,
            'notes'         => $this->notes->toArray(),
            'sources'       => $this->sources->toArray(),
        ];
    }
}
