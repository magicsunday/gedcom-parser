<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\NamePhoneticVariationInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\NameRomanizedVariationInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\PersonalNamePiecesInterface;

/**
 * The personal name structure tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface PersonalNameStructureInterface extends PersonalNamePiecesInterface
{
    /**
     * The name.
     */
    const TAG_NAME_PERSONAL = 'NAME_PERSONAL';

    /**
     * Indicates the name type, for example the name issued or assumed as an immigrant.
     *
     * aka          = also known as, alias, etc.
     * birth        = name given on birth certificate
     * immigrant    = name assumed at the time of immigration
     * maiden       = maiden name, name before first marriage
     * married      = name was persons previous married name
     * user_defined = other text name that defines the name type
     */
    const TAG_TYPE = 'TYPE';

    /**
     * The phonetic variation of the name is written in the same form as the was the name used in the
     * superior <NAME_PERSONAL> primitive, but phonetically written using the method indicated by the
     * subordinate <PHONETIC_TYPE> value, for example if hiragana was used to provide a reading of a
     * name written in kanji, then the <PHONETIC_TYPE> value would indicate ‘kana’.
     */
    const TAG_FONE = 'FONE';

    /**
     * The romanized variation of the name is written in the same form prescribed for the name used in the
     * superior <NAME_PERSONAL> context. The method used to romanize the name is indicated by the
     * line_value of the subordinate <ROMANIZED_TYPE>, for example if romaji was used to provide a
     * reading of a name written in kanji, then the ROMANIZED_TYPE subordinate to the ROMN tag
     * would indicate romaji.
     */
    const TAG_ROMN = 'ROMN';

    /**
     * @return null|string
     */
    public function getName();

    /**
     * @return null|string
     */
    public function getType();

    /**
     * @return NamePhoneticVariationInterface[]
     */
    public function getPhoneticVariation(): array;

    /**
     * @return NameRomanizedVariationInterface[]
     */
    public function getRomanizedVariation(): array;
}
