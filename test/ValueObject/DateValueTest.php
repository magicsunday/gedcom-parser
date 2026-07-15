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
use MagicSunday\Gedcom\ValueObject\DateType;
use MagicSunday\Gedcom\ValueObject\DateValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests routing of the GEDCOM DATE_VALUE qualifier grammar into a typed value object.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(DateValue::class)]
#[CoversClass(DateType::class)]
#[UsesClass(CalendarDate::class)]
#[UsesClass(Calendar::class)]
class DateValueTest extends TestCase
{
    /**
     * Data provider for the qualifier grammar.
     *
     * @return array<string, array{0: string, 1: DateType, 2: int|null, 3: int|null, 4: string|null}>
     */
    public static function typeProvider(): array
    {
        return [
            // label            raw                          type                    startYear endYear phrase
            'exact'             => ['1900', DateType::Exact, 1900, null, null],
            'about'             => ['ABT 1900', DateType::About, 1900, null, null],
            'calculated'        => ['CAL 1900', DateType::Calculated, 1900, null, null],
            'estimated'         => ['EST 1900', DateType::Estimated, 1900, null, null],
            'before'            => ['BEF 1900', DateType::Before, 1900, null, null],
            'after'             => ['AFT 1900', DateType::After, 1900, null, null],
            'between'           => ['BET 1900 AND 1910', DateType::Between, 1900, 1910, null],
            'between open'      => ['BET 1900', DateType::Between, 1900, null, null],
            'between extra'     => ['BET 1900 AND 1910 AND 1920', DateType::Between, 1900, null, null],
            'from'              => ['FROM 1900', DateType::From, 1900, null, null],
            'to'                => ['TO 1910', DateType::To, 1910, null, null],
            'from to'           => ['FROM 1900 TO 1910', DateType::FromTo, 1900, 1910, null],
            'from empty to'     => ['FROM 1900 TO', DateType::FromTo, 1900, null, null],
            'between empty end' => ['BET 1900 AND', DateType::Between, 1900, null, null],
            'interpreted'       => ['INT 1900 (a guess)', DateType::Interpreted, 1900, null, 'a guess'],
            'phrase'            => ['(sometime in the past)', DateType::Phrase, null, null, 'sometime in the past'],
        ];
    }

    /**
     * @param string      $raw       The raw DATE_VALUE.
     * @param DateType    $type      The expected type.
     * @param int|null    $startYear The expected primary date year.
     * @param int|null    $endYear   The expected end date year.
     * @param string|null $phrase    The expected phrase.
     */
    #[Test]
    #[DataProvider('typeProvider')]
    public function fromGedcomRoutesTheQualifier(
        string $raw,
        DateType $type,
        ?int $startYear,
        ?int $endYear,
        ?string $phrase,
    ): void {
        $value = DateValue::fromGedcom($raw);

        self::assertSame($type, $value->type, 'type');
        self::assertSame($startYear, $value->date?->year, 'start year');
        self::assertSame($endYear, $value->endDate?->year, 'end year');
        self::assertSame($phrase, $value->phrase, 'phrase');
    }

    /**
     * A full day/month/year date is parsed inside a qualifier.
     */
    #[Test]
    public function fromGedcomParsesAFullDateWithinAQualifier(): void
    {
        $value = DateValue::fromGedcom('BEF 1 JAN 1900');

        self::assertSame(DateType::Before, $value->type);
        self::assertNotNull($value->date);
        self::assertSame(1, $value->date->day);
        self::assertSame(1, $value->date->month);
        self::assertSame(1900, $value->date->year);
    }

    /**
     * A calendar escape inside a qualified date is carried onto the parsed date.
     */
    #[Test]
    public function fromGedcomCarriesTheCalendarThroughAQualifier(): void
    {
        $value = DateValue::fromGedcom('ABT @#DJULIAN@ 1700');

        self::assertSame(DateType::About, $value->type);
        self::assertNotNull($value->date);
        self::assertSame(Calendar::Julian, $value->date->calendar);
        self::assertSame(1700, $value->date->year);
    }

    /**
     * A malformed interpreted date with no date part yields a NULL date, keeping the phrase.
     */
    #[Test]
    public function fromGedcomInterpretedWithoutADateHasANullDate(): void
    {
        $value = DateValue::fromGedcom('INT (a guess)');

        self::assertSame(DateType::Interpreted, $value->type);
        self::assertNull($value->date);
        self::assertSame('a guess', $value->phrase);
    }

    /**
     * The original raw text is preserved verbatim.
     */
    #[Test]
    public function fromGedcomPreservesTheRawText(): void
    {
        self::assertSame('  BET 1900 AND 1910  ', DateValue::fromGedcom('  BET 1900 AND 1910  ')->raw);
    }

    /**
     * A value-less DATE carried solely by a GEDCOM 7.0 PHRASE substructure becomes a phrase-typed
     * date: the phrase is recorded, there is no calendar date, and the empty value is kept as raw.
     */
    #[Test]
    public function fromGedcomBuildsAPhraseDateFromAValueLessDate(): void
    {
        $value = DateValue::fromGedcom('', 'around harvest time');

        self::assertSame(DateType::Phrase, $value->type);
        self::assertNull($value->date);
        self::assertNull($value->endDate);
        self::assertSame('around harvest time', $value->phrase);
        self::assertSame('', $value->raw);
    }

    /**
     * A valued DATE that also carries an explicit GEDCOM 7.0 PHRASE keeps its parsed form and
     * records the phrase alongside, overriding any inline phrase from the value grammar.
     */
    #[Test]
    public function fromGedcomAttachesAnExplicitPhraseToAValuedDate(): void
    {
        $value = DateValue::fromGedcom('1 JAN 2000', "New Year's Day");

        self::assertSame(DateType::Exact, $value->type);
        self::assertSame(2000, $value->date?->year);
        self::assertSame("New Year's Day", $value->phrase);
        self::assertSame('1 JAN 2000', $value->raw);
    }

    /**
     * An empty or whitespace-only explicit phrase is treated as absent, leaving the parsed date
     * untouched.
     */
    #[Test]
    public function fromGedcomTreatsAnEmptyExplicitPhraseAsAbsent(): void
    {
        $value = DateValue::fromGedcom('1 JAN 2000', '   ');

        self::assertSame(DateType::Exact, $value->type);
        self::assertNull($value->phrase);
    }
}
