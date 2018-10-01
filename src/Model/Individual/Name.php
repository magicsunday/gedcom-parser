<?php
declare(strict_types = 1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Model\Individual;

use MagicSunday\Gedcom\Model\ArrayInterface;
use MagicSunday\Gedcom\Model\Individual\Name\Phonetic;
use MagicSunday\Gedcom\Model\Individual\Name\Romanized;

/**
 * The individual model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Name extends NamePieces
{
    /**
     * A word or combination of words used to help identify an individual, title, or other item. More than one
     * NAME line should be used for people who were known by multiple names.
     *
     * @var string
     */
    private $name;

    /**
     * Indicates the name type, for example the name issued or assumed as an immigrant.
     *
     * aka          = also known as, alias, etc.
     * birth        = name given on birth certificate
     * immigrant    = name assumed at the time of immigration
     * maiden       = maiden name, name before first marriage
     * married      = name was persons previous married name
     * user_defined = other text name that defines the name type
     *
     * @var string
     */
    private $type;

    /**
     * @var Phonetic
     */
    private $phonetic;

    /**
     * @var Romanized
     */
    private $romanized;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Phonetic
     */
    public function getPhonetic(): Phonetic
    {
        return $this->phonetic;
    }

    /**
     * @param Phonetic $phonetic
     *
     * @return self
     */
    public function setPhonetic(Phonetic $phonetic): self
    {
        $this->phonetic = $phonetic;
        return $this;
    }

    /**
     * @return Romanized
     */
    public function getRomanized(): Romanized
    {
        return $this->romanized;
    }

    /**
     * @param Romanized $romanized
     *
     * @return self
     */
    public function setRomanized(Romanized $romanized): self
    {
        $this->romanized = $romanized;
        return $this;
    }

    /**
     * Returns the object as array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        $parent = parent::toArray();

        return [
            'name'       => $this->name,
            'type'       => $this->type,
            'phonetic'   => $this->phonetic->toArray(),
            'romanized'  => $this->romanized->toArray(),
        ];
    }
}
