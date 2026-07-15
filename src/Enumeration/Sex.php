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
 * The known standard values of the GEDCOM 7.0 `SEX` enumeration set — an individual's sex.
 *
 * These constants are typed comparison targets for the raw sex value an individual carries; that
 * value stays a tolerant string, so an extension tag or an unlisted value is preserved rather than
 * rejected. The constants are named after their meaning rather than the single-letter token.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class Sex
{
    /**
     * Female.
     */
    public const string FEMALE = 'F';

    /**
     * Male.
     */
    public const string MALE = 'M';

    /**
     * Cannot be determined from available sources.
     */
    public const string UNDETERMINED = 'U';

    /**
     * Does not fit the typical definition of only male or only female.
     */
    public const string OTHER = 'X';

    /**
     * Private constructor; this is a constant holder, not an instantiable type.
     */
    private function __construct()
    {
    }

    /**
     * Returns the known standard values of the `SEX` enumeration set.
     *
     * @return list<string> The known standard sex values.
     */
    public static function values(): array
    {
        return [
            self::FEMALE,
            self::MALE,
            self::UNDETERMINED,
            self::OTHER,
        ];
    }
}
