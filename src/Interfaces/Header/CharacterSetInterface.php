<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Header;

/**
 * The character set structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface CharacterSetInterface
{
    /**
     * A code value that represents the character set to be used to interpret this data. Currently, the
     * preferred character set is ANSEL, which includes ASCII as a subset. UNICODE is not widely
     * supported by most operating systems; therefore, GEDCOM produced using the UNICODE character
     * set will be limited in its interchangeability for a while but should eventually provide the international
     * flexibility that is desired.
     */
    const TAG_CHARACTER_SET = 'CHARACTER_SET';

    /**
     * An identifier that represents the version level assigned to the associated product. It is defined and
     * changed by the creators of the product.
     */
    const TAG_VERS = 'VERS';

    /**
     * @return null|string
     */
    public function getCharacterSet();

    /**
     * @return null|string
     */
    public function getVersion();
}
