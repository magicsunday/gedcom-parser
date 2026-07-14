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
            'bce lowercase'        => ['100 b.c.', Calendar::Gregorian, null, null, 100, true, null],
            'dual year'            => ['1699/00', Calendar::Gregorian, null, null, 1699, false, 1700],
            'dual year with month' => ['11 FEB 1731/32', Calendar::Gregorian, 11, 2, 1731, false, 1732],
            'short dual suffix'    => ['1700/0', Calendar::Gregorian, null, null, 1700, false, null],
            'long dual suffix'     => ['1699/000', Calendar::Gregorian, null, null, 1699, false, null],
            'julian escape'        => ['@#DJULIAN@ 15 MAR 1700', Calendar::Julian, 15, 3, 1700, false, null],
            'lowercase escape'     => ['@#djulian@ 1700', Calendar::Julian, null, null, 1700, false, null],
            'hebrew escape'        => ['@#DHEBREW@ 1 TSH 5000', Calendar::Hebrew, 1, 1, 5000, false, null],
            'day with bad month'   => ['15 FOO 1700', Calendar::Gregorian, null, null, 1700, false, null],
            'over-long day token'  => ['123 MAR 1700', Calendar::Gregorian, null, 3, 1700, false, null],
            'french republican'    => ['@#DFRENCH R@ 2 VEND 1', Calendar::FrenchRepublican, 2, 1, 1, false, null],
            'unknown escape'       => ['@#DFOO@ 1700', Calendar::Unknown, null, null, 1700, false, null],
            'unknown month token'  => ['FOO 1700', Calendar::Gregorian, null, null, 1700, false, null],
            'day and month only'   => ['2 JAN', Calendar::Gregorian, 2, 1, null, false, null],
            'month only'           => ['MAR', Calendar::Gregorian, null, 3, null, false, null],
            'non-digit day token'  => ['X MAR 1700', Calendar::Gregorian, null, 3, 1700, false, null],
            'non-date token'       => ['HELLO', Calendar::Gregorian, null, null, null, false, null],
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

    /**
     * Data provider for the Julian Day Number conversion.
     *
     * The expected values are well-known reference Julian Day Numbers.
     *
     * @return array<string, array{0: string, 1: int|null}>
     */
    public static function julianDayProvider(): array
    {
        return [
            'gregorian 2000'           => ['1 JAN 2000', 2451545],
            'gregorian unix epoch'     => ['1 JAN 1970', 2440588],
            'gregorian 1 bc'           => ['1 JAN 1 B.C.', 1721060],
            'julian 2000'              => ['@#DJULIAN@ 1 JAN 2000', 2451558],
            'julian ad 1'              => ['@#DJULIAN@ 1 JAN 1', 1721424],
            'julian day epoch'         => ['@#DJULIAN@ 1 JAN 4713 B.C.', 0],
            'partial defaults start'   => ['2000', 2451545],
            'dual year uses new style' => ['11 FEB 1731/32', 2353701],
            'hebrew tishri'            => ['@#DHEBREW@ 1 TSH 5785', 2460587],
            'hebrew adar i in leap'    => ['@#DHEBREW@ 1 ADR 5784', 2460351],
            'hebrew adar ii in leap'   => ['@#DHEBREW@ 1 ADS 5784', 2460381],
            'hebrew adar ii common'    => ['@#DHEBREW@ 1 ADS 5785', null],
            'hebrew invalid day'       => ['@#DHEBREW@ 31 TSH 5785', null],
            'hebrew bce is null'       => ['@#DHEBREW@ 1 TSH 1 B.C.', null],
            'hebrew year zero is null' => ['@#DHEBREW@ 1 TSH 0', null],
            'french republican epoch'  => ['@#DFRENCH R@ 1 VEND 1', 2375840],
            'french 18 brumaire viii'  => ['@#DFRENCH R@ 18 BRUM 8', 2378444],
            'french leap sixth comp'   => ['@#DFRENCH R@ 6 COMP 3', 2376935],
            'french common sixth comp' => ['@#DFRENCH R@ 6 COMP 4', null],
            'french bce is null'       => ['@#DFRENCH R@ 1 VEND 1 B.C.', null],
            'french year zero is null' => ['@#DFRENCH R@ 1 VEND 0', null],
            'no year is null'          => ['MAR', null],
        ];
    }

    /**
     * @param string   $raw      The raw DATE token
     * @param int|null $expected The expected Julian Day Number, or NULL
     */
    #[Test]
    #[DataProvider('julianDayProvider')]
    public function toJulianDayConvertsSupportedCalendars(string $raw, ?int $expected): void
    {
        self::assertSame($expected, CalendarDate::fromGedcom($raw)->toJulianDay());
    }
}
