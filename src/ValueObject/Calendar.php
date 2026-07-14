<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\ValueObject;

/**
 * The calendars a GEDCOM DATE may be expressed in, selected by an `@#D…@` escape.
 *
 * The backed value is the calendar name exactly as it appears inside the escape
 * (e.g. `@#DFRENCH R@` → `FRENCH R`). Gregorian is the default when no escape is given.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
enum Calendar: string
{
    /**
     * The Gregorian calendar (the GEDCOM default).
     */
    case Gregorian = 'GREGORIAN';

    /**
     * The Julian calendar.
     */
    case Julian = 'JULIAN';

    /**
     * The Hebrew calendar.
     */
    case Hebrew = 'HEBREW';

    /**
     * The French Republican calendar.
     */
    case FrenchRepublican = 'FRENCH R';

    /**
     * The Roman calendar (reserved by GEDCOM 5.5.1 for future definition).
     */
    case Roman = 'ROMAN';

    /**
     * An explicitly unknown calendar.
     */
    case Unknown = 'UNKNOWN';
}
