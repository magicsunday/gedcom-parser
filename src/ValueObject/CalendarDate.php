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
            if (preg_match('#^(\d+)(?:/(\d{2}))?$#', $lastToken, $matches) === 1) {
                $year = (int) $matches[1];

                if (($matches[2] ?? '') !== '') {
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

                if (($dayToken !== null) && ctype_digit($dayToken)) {
                    $day = (int) $dayToken;
                }
            }
        }

        return new self($calendar, $day, $month, $year, $bce, $dualYear, $date);
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
}
