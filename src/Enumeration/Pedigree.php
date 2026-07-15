<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Enumeration;

/**
 * The known standard values of the GEDCOM 7.0 `PEDI` enumeration set — the pedigree linking a child
 * to a family.
 *
 * These constants are typed comparison targets for the raw pedigree value a child-to-family link
 * carries; that value stays a tolerant string, so an extension tag or an unlisted value is preserved
 * rather than rejected, and the catch-all `OTHER` ({@see self::OTHER}) names a relationship not
 * otherwise listed.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class Pedigree
{
    /**
     * The child is the adopted child of the parents.
     */
    public const string ADOPTED = 'ADOPTED';

    /**
     * The family structure at the child's birth. Per the specification this must not be read as
     * asserting either a genetic or a social parent relationship.
     */
    public const string BIRTH = 'BIRTH';

    /**
     * The child was included in a foster or guardian family.
     */
    public const string FOSTER = 'FOSTER';

    /**
     * A relationship not otherwise listed.
     */
    public const string OTHER = 'OTHER';

    /**
     * The child was sealed to parents other than the birth parents (an LDS `SLGC` ordinance).
     */
    public const string SEALING = 'SEALING';

    /**
     * Private constructor; this is a constant holder, not an instantiable type.
     */
    private function __construct()
    {
    }

    /**
     * Returns the known standard values of the `PEDI` enumeration set.
     *
     * @return list<string> The known standard pedigree values.
     */
    public static function values(): array
    {
        return [
            self::ADOPTED,
            self::BIRTH,
            self::FOSTER,
            self::OTHER,
            self::SEALING,
        ];
    }
}
