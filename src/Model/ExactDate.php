<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

/**
 * A GEDCOM 7.0 exact date with an optional time of day (the `DATE` substructure of `CREA`/`CHAN`).
 *
 * Unlike a genealogical `DATE_VALUE`, the change- and creation-timestamp dates use the restricted
 * exact-date grammar — a plain day/month/year in the Gregorian calendar — so the date is kept as its
 * raw string ({@see self::$value}) rather than parsed into a {@see \MagicSunday\Gedcom\ValueObject\DateValue}.
 * The accompanying wall-clock time ({@see self::$time}) is optional.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class ExactDate
{
    /**
     * @param string|null $value The exact date text (the DATE line value), e.g. `1 JAN 2000`, or NULL when absent.
     * @param string|null $time  The time of day accompanying the date (TIME), e.g. `12:00:00`, or NULL when absent.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $time = null,
    ) {
    }
}
