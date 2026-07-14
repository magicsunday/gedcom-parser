<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\ValueObject;

use MagicSunday\Gedcom\ValueObject\Calendar;
use MagicSunday\Gedcom\ValueObject\CalendarDate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests parsing of a single calendar-aware GEDCOM DATE token.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(CalendarDate::class)]
#[CoversClass(Calendar::class)]
class CalendarDateTest extends TestCase
{
    /**
     * Data provider for the DATE token grammar.
     *
     * @return array<string, array{0: string, 1: Calendar, 2: int|null, 3: int|null, 4: int|null, 5: bool, 6: int|null}>
     */
    public static function dateProvider(): array
    {
        return [
            // label                    raw                          calendar                    d     m     year   bce    dual
            'full gregorian'       => ['15 MAR 1700', Calendar::Gregorian, 15, 3, 1700, false, null],
            'month and year'       => ['MAR 1700', Calendar::Gregorian, null, 3, 1700, false, null],
            'year only'            => ['1700', Calendar::Gregorian, null, null, 1700, false, null],
            'bce year'             => ['44 B.C.', Calendar::Gregorian, null, null, 44, true, null],
            'bce full'             => ['15 MAR 44 B.C.', Calendar::Gregorian, 15, 3, 44, true, null],
            'bce lowercase'        => ['100 bce', Calendar::Gregorian, null, null, 100, true, null],
            'dual year'            => ['1699/00', Calendar::Gregorian, null, null, 1699, false, 1700],
            'dual year with month' => ['11 FEB 1731/32', Calendar::Gregorian, 11, 2, 1731, false, 1732],
            'julian escape'        => ['@#DJULIAN@ 15 MAR 1700', Calendar::Julian, 15, 3, 1700, false, null],
            'hebrew escape'        => ['@#DHEBREW@ 1 TSH 5000', Calendar::Hebrew, 1, 1, 5000, false, null],
            'french republican'    => ['@#DFRENCH R@ 2 VEND 1', Calendar::FrenchRepublican, 2, 1, 1, false, null],
            'unknown escape'       => ['@#DFOO@ 1700', Calendar::Unknown, null, null, 1700, false, null],
            'unknown month token'  => ['FOO 1700', Calendar::Gregorian, null, null, 1700, false, null],
            'empty'                => ['', Calendar::Gregorian, null, null, null, false, null],
        ];
    }

    /**
     * @param string   $raw      The raw DATE token
     * @param Calendar $calendar The expected calendar
     * @param int|null $day      The expected day
     * @param int|null $month    The expected 1-based month number
     * @param int|null $year     The expected year
     * @param bool     $bce      The expected B.C. flag
     * @param int|null $dualYear The expected expanded dual year
     */
    #[Test]
    #[DataProvider('dateProvider')]
    public function fromGedcomParsesTheDate(
        string $raw,
        Calendar $calendar,
        ?int $day,
        ?int $month,
        ?int $year,
        bool $bce,
        ?int $dualYear,
    ): void {
        $date = CalendarDate::fromGedcom($raw);

        self::assertSame($calendar, $date->calendar, 'calendar');
        self::assertSame($day, $date->day, 'day');
        self::assertSame($month, $date->month, 'month');
        self::assertSame($year, $date->year, 'year');
        self::assertSame($bce, $date->bce, 'bce');
        self::assertSame($dualYear, $date->dualYear, 'dualYear');
    }

    /**
     * The original raw text is preserved verbatim.
     */
    #[Test]
    public function fromGedcomPreservesTheRawText(): void
    {
        self::assertSame('  @#DJULIAN@ 1700  ', CalendarDate::fromGedcom('  @#DJULIAN@ 1700  ')->raw);
    }
}
