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
 * The kind of a GEDCOM DATE_VALUE — an exact date, an approximation, a range, a period, an
 * interpreted date or a free-text phrase.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
enum DateType
{
    /**
     * A single exact date.
     */
    case Exact;

    /**
     * An approximate date (`ABT`).
     */
    case About;

    /**
     * A calculated date (`CAL`).
     */
    case Calculated;

    /**
     * An estimated date (`EST`).
     */
    case Estimated;

    /**
     * A date range bounded above (`BEF`).
     */
    case Before;

    /**
     * A date range bounded below (`AFT`).
     */
    case After;

    /**
     * A date range bounded on both sides (`BET … AND …`).
     */
    case Between;

    /**
     * A period starting at a date (`FROM`).
     */
    case From;

    /**
     * A period ending at a date (`TO`).
     */
    case To;

    /**
     * A period between two dates (`FROM … TO …`).
     */
    case FromTo;

    /**
     * An interpreted date with an accompanying phrase (`INT … (…)`).
     */
    case Interpreted;

    /**
     * A free-text date phrase (`(…)`).
     */
    case Phrase;
}
