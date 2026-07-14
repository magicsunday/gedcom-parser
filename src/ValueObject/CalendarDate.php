<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\ValueObject;

use function array_pop;
use function ctype_digit;
use function floor;
use function intdiv;
use function preg_match;
use function preg_split;
use function strlen;
use function strtoupper;
use function substr;
use function trim;

/**
 * A single calendar-aware GEDCOM date.
 *
 * Parses one DATE token — an optional `@#D…@` calendar escape followed by an optional day, an
 * optional month abbreviation (interpreted per the date's calendar) and a year, with an optional
 * `B.C.` marker and a dual `1699/00` year. Any component may be absent (a partial date). The
 * original raw text is preserved.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class CalendarDate
{
    /**
     * The month-abbreviation → number tables per calendar family. Gregorian and Julian share the
     * standard twelve; Hebrew and the French Republican calendar have their own thirteen.
     *
     * @var array<string, array<string, int>>
     */
    private const array MONTHS = [
        'GREGORIAN' => [
            'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6,
            'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
        ],
        'HEBREW' => [
            'TSH' => 1, 'CSH' => 2, 'KSL' => 3, 'TVT' => 4, 'SHV' => 5, 'ADR' => 6, 'ADS' => 7,
            'NSN' => 8, 'IYR' => 9, 'SVN' => 10, 'TMZ' => 11, 'AAV' => 12, 'ELL' => 13,
        ],
        'FRENCH R' => [
            'VEND' => 1, 'BRUM' => 2, 'FRIM' => 3, 'NIVO' => 4, 'PLUV' => 5, 'VENT' => 6, 'GERM' => 7,
            'FLOR' => 8, 'PRAI' => 9, 'MESS' => 10, 'THER' => 11, 'FRUC' => 12, 'COMP' => 13,
        ],
    ];

    /**
     * @param Calendar $calendar The calendar the date is expressed in (Gregorian by default)
     * @param int|null $day      The day of the month, or NULL when absent
     * @param int|null $month    The 1-based month number within the calendar, or NULL when absent
     * @param int|null $year     The year, or NULL when absent
     * @param bool     $bce      Whether the year is before the common era (`B.C.`)
     * @param int|null $dualYear The expanded second year of a dual `1699/00` date, or NULL
     * @param string   $raw      The original, unparsed DATE token
     */
    public function __construct(
        public Calendar $calendar,
        public ?int $day,
        public ?int $month,
        public ?int $year,
        public bool $bce,
        public ?int $dualYear,
        public string $raw,
    ) {
    }

    /**
     * Parses a raw GEDCOM DATE token into a typed calendar-aware date.
     *
     * @param string $date The raw DATE token, e.g. `15 MAR 1700`, `@#DJULIAN@ 1699/00` or `44 B.C.`
     */
    public static function fromGedcom(string $date): self
    {
        $value    = trim($date);
        $calendar = Calendar::Gregorian;

        // Leading calendar escape: @#D<NAME>@ (case-insensitive, like the rest of the parser).
        if (preg_match('/^@#D([A-Za-z ]+)@\s*/i', $value, $matches) === 1) {
            $calendar = Calendar::tryFrom(strtoupper($matches[1])) ?? Calendar::Unknown;
            $value    = trim(substr($value, strlen($matches[0])));
        }

        // Trailing GEDCOM 5.5.1 era marker (`B.C.`; the 7.0 `BCE` spelling is out of scope here).
        $bce = false;

        if (preg_match('/\s(B\.C\.)$/i', ' ' . $value, $matches) === 1) {
            $bce   = true;
            $value = trim(substr($value, 0, strlen($value) - strlen($matches[1])));
        }

        $day      = null;
        $month    = null;
        $year     = null;
        $dualYear = null;

        $tokens = [];

        if ($value !== '') {
            $split = preg_split('/\s+/', $value);

            if ($split !== false) {
                $tokens = $split;
            }
        }

        // The final token is the year when numeric (a dual year has an exact two-digit suffix);
        // otherwise the date carries no year and that token may be the month. The day, if any,
        // precedes the month.
        $lastToken  = array_pop($tokens);
        $monthToken = null;

        if ($lastToken !== null) {
            if (preg_match('#^(\d+)(?:/(\d+))?$#', $lastToken, $matches) === 1) {
                $year = (int) $matches[1];

                // A conformant dual suffix is exactly two digits; an invalid one is ignored while
                // the primary year is kept, rather than losing the whole token.
                if (strlen($matches[2] ?? '') === 2) {
                    $dualYear = self::expandDualYear($year, $matches[2]);
                }

                $monthToken = array_pop($tokens);
            } else {
                $monthToken = $lastToken;
            }
        }

        // A day only exists alongside a recognised month; a day before an unknown month is not a
        // conformant date component, so leave it unparsed.
        if ($monthToken !== null) {
            $month = self::monthNumber($calendar, $monthToken);

            if ($month !== null) {
                $dayToken = array_pop($tokens);

                // A day is at most two digits in every supported calendar.
                if (($dayToken !== null) && ctype_digit($dayToken) && (strlen($dayToken) <= 2)) {
                    $day = (int) $dayToken;
                }
            }
        }

        return new self($calendar, $day, $month, $year, $bce, $dualYear, $date);
    }

    /**
     * Converts the date to its Julian Day Number for calendar-independent comparison and sorting.
     *
     * The year is required; an absent month or day defaults to the first, so a partial date sorts
     * at the start of its period. A `B.C.` year is mapped to its astronomical form (1 B.C. is
     * year 0). Only the Gregorian and Julian calendars are converted here; the Hebrew and French
     * Republican calendars — and the reserved/unknown ones — return NULL (see GH-56).
     *
     * @return int|null The Julian Day Number, or NULL when the date has no year or an
     *                  unconvertible calendar
     */
    public function toJulianDay(): ?int
    {
        if ($this->year === null) {
            return null;
        }

        $year  = $this->bce ? 1 - $this->year : $this->year;
        $month = $this->month ?? 1;
        $day   = $this->day ?? 1;

        return match ($this->calendar) {
            Calendar::Gregorian => self::gregorianToJulianDay($year, $month, $day),
            Calendar::Julian    => self::julianToJulianDay($year, $month, $day),
            Calendar::Hebrew,
            Calendar::FrenchRepublican,
            Calendar::Roman,
            Calendar::Unknown => null,
        };
    }

    /**
     * Expands the two-digit second year of a dual date into a full year.
     *
     * `1699/00` spans two reckonings of the same year; the suffix is the last digits of the second
     * year (`1700`). The result is the nearest year at or after the primary year whose trailing
     * digits match the suffix.
     *
     * @param int    $year   The primary year
     * @param string $suffix The captured trailing digits of the second year
     *
     * @return int The expanded second year
     */
    private static function expandDualYear(int $year, string $suffix): int
    {
        $magnitude = 10 ** strlen($suffix);
        $dual      = (intdiv($year, $magnitude) * $magnitude) + (int) $suffix;

        return $dual < $year ? $dual + $magnitude : $dual;
    }

    /**
     * Resolves a GEDCOM month abbreviation to its 1-based number within the given calendar.
     *
     * @param Calendar $calendar The calendar whose month table applies
     * @param string   $token    The month abbreviation, e.g. `MAR` or `BRUM`
     *
     * @return int|null The month number, or NULL when the token is not a known month
     */
    private static function monthNumber(Calendar $calendar, string $token): ?int
    {
        $table = match ($calendar) {
            Calendar::Hebrew           => self::MONTHS['HEBREW'],
            Calendar::FrenchRepublican => self::MONTHS['FRENCH R'],
            default                    => self::MONTHS['GREGORIAN'],
        };

        return $table[strtoupper($token)] ?? null;
    }

    /**
     * Converts a proleptic Gregorian date to its Julian Day Number (Fliegel–Van Flandern).
     *
     * @param int $year  The astronomical year (1 B.C. is 0)
     * @param int $month The 1-based month
     * @param int $day   The day of the month
     *
     * @return int The Julian Day Number
     */
    private static function gregorianToJulianDay(int $year, int $month, int $day): int
    {
        $a = intdiv(14 - $month, 12);
        $y = ($year + 4800) - $a;
        $m = ($month + (12 * $a)) - 3;

        return $day
            + intdiv((153 * $m) + 2, 5)
            + (365 * $y)
            + (int) floor($y / 4)
            - (int) floor($y / 100)
            + (int) floor($y / 400)
            - 32045;
    }

    /**
     * Converts a proleptic Julian date to its Julian Day Number.
     *
     * @param int $year  The astronomical year (1 B.C. is 0)
     * @param int $month The 1-based month
     * @param int $day   The day of the month
     *
     * @return int The Julian Day Number
     */
    private static function julianToJulianDay(int $year, int $month, int $day): int
    {
        $a = intdiv(14 - $month, 12);
        $y = ($year + 4800) - $a;
        $m = ($month + (12 * $a)) - 3;

        return $day
            + intdiv((153 * $m) + 2, 5)
            + (365 * $y)
            + (int) floor($y / 4)
            - 32083;
    }
}
