<?php
declare(strict_types=1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Model\Individual\PersonalNameStructure;

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
class Romanized extends PersonalNamePieces
{
    /**
     * The romanized variation of the name.
     */
    const TAG_NAME_ROMANIZED_VARIATION = 'NAME_ROMANIZED_VARIATION';

    /**
     * Indicates the method used in transforming the text to a romanized variation.
     *
     * - user defined
     * - pinyin
     * - romaji
     * - wadegiles
     */
    const TAG_TYPE = 'TYPE';

    /**
     * @return null|string
     */
    public function getNameVariation()
    {
        return $this->getValue(self::TAG_NAME_ROMANIZED_VARIATION);
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }
}
