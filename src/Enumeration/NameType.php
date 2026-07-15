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
 * The known standard values of the GEDCOM 7.0 `NAME-TYPE` enumeration set — the type of a personal
 * name.
 *
 * These constants are typed comparison targets for the raw name-type value a personal name carries;
 * that value stays a tolerant string, so an extension tag or an unlisted value is preserved rather
 * than rejected, and the catch-all `OTHER` ({@see self::OTHER}) names a type not otherwise listed.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class NameType
{
    /**
     * A name by which the person is also known.
     */
    public const string AKA = 'AKA';

    /**
     * The name given at or near birth.
     */
    public const string BIRTH = 'BIRTH';

    /**
     * A name assumed at the time of immigration.
     */
    public const string IMMIGRANT = 'IMMIGRANT';

    /**
     * A maiden name — the name before a first marriage.
     */
    public const string MAIDEN = 'MAIDEN';

    /**
     * A name acquired through marriage.
     */
    public const string MARRIED = 'MARRIED';

    /**
     * A type not otherwise listed.
     */
    public const string OTHER = 'OTHER';

    /**
     * A name used in a professional capacity.
     */
    public const string PROFESSIONAL = 'PROFESSIONAL';

    /**
     * Private constructor; this is a constant holder, not an instantiable type.
     */
    private function __construct()
    {
    }

    /**
     * Returns the known standard values of the `NAME-TYPE` enumeration set.
     *
     * @return list<string> The known standard name-type values.
     */
    public static function values(): array
    {
        return [
            self::AKA,
            self::BIRTH,
            self::IMMIGRANT,
            self::MAIDEN,
            self::MARRIED,
            self::OTHER,
            self::PROFESSIONAL,
        ];
    }
}
