<?php
declare(strict_types = 1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Model\Individual\Name;

use MagicSunday\Gedcom\Model\Individual\NamePieces;

/**
 * The romanized variation of the name is written in the same form prescribed for the name used in the
 * superior <NAME_PERSONAL> context. The method used to romanize the name is indicated by the
 * line_value of the subordinate <ROMANIZED_TYPE>, for example if romaji was used to provide a
 * reading of a name written in kanji, then the ROMANIZED_TYPE subordinate to the ROMN tag
 * would indicate romaji.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Romanized extends NamePieces
{
    /**
     * Indicates the method used in transforming the text to a romanized variation.
     *
     * - user defined
     * - pinyin
     * - romaji
     * - wadegiles
     *
     * @var string
     */
    private $type;

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
     * Returns the object as array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        $parent = parent::toArray();

        return [
            'type'       => $this->type,
        ];
    }
}
