<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure;

/**
 * The name phonetic variation tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface NamePhoneticVariationInterface extends PersonalNamePiecesInterface
{
    /**
     * The phonetic variation of the name.
     */
    public const TAG_NAME_PHONETIC_VARIATION = 'NAME_PHONETIC_VARIATION';

    /**
     * Indicates the method used in transforming the text to the phonetic variation.
     *
     * - user defined (Record method used to arrive at the phonetic variation of the name)
     * - hangul (Phonetic method for sounding Korean glifs)
     * - kana (Hiragana and/or Katakana characters were used in sounding the Kanji character used by japanese)
     */
    public const TAG_TYPE = 'TYPE';

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return string
     */
    public function getType(): string;
}
