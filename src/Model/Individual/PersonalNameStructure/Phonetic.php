<?php
declare(strict_types=1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Model\Individual\PersonalNameStructure;

/**
 * The phonetic variation of the name is written in the same form as the was the name used in the
 * superior <NAME_PERSONAL> primitive, but phonetically written using the method indicated by the
 * subordinate <PHONETIC_TYPE> value, for example if hiragana was used to provide a reading of a
 * name written in kanji, then the <PHONETIC_TYPE> value would indicate ‘kana’.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Phonetic extends PersonalNamePieces
{
    /**
     * The phonetic variation of the name.
     */
    const TAG_NAME_PHONETIC_VARIATION = 'NAME_PHONETIC_VARIATION';

    /**
     * Indicates the method used in transforming the text to the phonetic variation.
     *
     * - user defined (Record method used to arrive at the phonetic variation of the name)
     * - hangul (Phonetic method for sounding Korean glifs)
     * - kana (Hiragana and/or Katakana characters were used in sounding the Kanji character used by japanese)
     */
    const TAG_TYPE = 'TYPE';

    /**
     * @return null|string
     */
    public function getNameVariation()
    {
        return $this->getValue(self::TAG_NAME_PHONETIC_VARIATION);
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }
}
