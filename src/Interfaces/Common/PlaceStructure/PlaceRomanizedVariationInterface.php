<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\PlaceStructure;

/**
 * The place ROMN (romanized) variation tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface PlaceRomanizedVariationInterface
{
    /**
     * The romanized variation of the place.
     */
    const TAG_PLACE_ROMANIZED_VARIATION = 'PLACE_ROMANIZED_VARIATION';

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
    public function getPlace();

    /**
     * @return string
     */
    public function getType(): string;
}
