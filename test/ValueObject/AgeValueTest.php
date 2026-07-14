<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\ValueObject;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyPersonAgeInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure\FamilyPersonAge;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail;
use MagicSunday\Gedcom\ValueObject\AgeKeyword;
use MagicSunday\Gedcom\ValueObject\AgeModifier;
use MagicSunday\Gedcom\ValueObject\AgeValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests parsing of the GEDCOM AGE_AT_EVENT grammar into a typed value object.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(AgeValue::class)]
#[CoversClass(AgeModifier::class)]
#[CoversClass(AgeKeyword::class)]
#[CoversClass(FamilyPersonAge::class)]
#[CoversClass(IndividualEventDetail::class)]
#[UsesClass(\MagicSunday\Gedcom\Model\DataObject::class)]
class AgeValueTest extends TestCase
{
    /**
     * Data provider for the AGE grammar variants.
     *
     * @return array<string, array{0: string, 1: AgeModifier|null, 2: AgeKeyword|null, 3: int|null, 4: int|null, 5: int|null}>
     */
    public static function ageProvider(): array
    {
        return [
            // label                     raw            modifier                  keyword               y     m     d
            'full duration'         => ['72y 3m 2d', null, null, 72, 3, 2],
            'years only'            => ['72y', null, null, 72, null, null],
            'months only'           => ['3m', null, null, null, 3, null],
            'days only'             => ['15d', null, null, null, null, 15],
            'years and months'      => ['1y 6m', null, null, 1, 6, null],
            'less than years'       => ['< 8y', AgeModifier::LessThan, null, 8, null, null],
            'greater than years'    => ['> 99y', AgeModifier::GreaterThan, null, 99, null, null],
            'less than no space'    => ['<1y', AgeModifier::LessThan, null, 1, null, null],
            'child keyword'         => ['CHILD', null, AgeKeyword::Child, null, null, null],
            'infant keyword'        => ['INFANT', null, AgeKeyword::Infant, null, null, null],
            'stillborn keyword'     => ['STILLBORN', null, AgeKeyword::Stillborn, null, null, null],
            'lowercase keyword'     => ['child', null, AgeKeyword::Child, null, null, null],
            'uppercase unit labels' => ['5Y 2M', null, null, 5, 2, null],
            'empty'                 => ['', null, null, null, null, null],
            // Non-conformant input is left unparsed (all NULL) rather than yielding a wrong parse.
            'reversed order'        => ['1d 2m 3y', null, null, null, null, null],
            'keyword with trailing' => ['CHILD 4y', null, null, null, null, null],
            'garbage between units' => ['72y 3 minutes 2d', null, null, null, null, null],
            'label inside a word'   => ['aged 5 years, 60y', null, null, null, null, null],
            'space within a pair'   => ['8 y', null, null, null, null, null],
            // A relational qualifier without a valid operand is meaningless; drop it entirely.
            'modifier only'         => ['<', null, null, null, null, null],
            'modifier then garbage' => ['> garbage', null, null, null, null, null],
        ];
    }

    /**
     * @param string           $raw      The raw AGE value
     * @param AgeModifier|null $modifier The expected relational qualifier
     * @param AgeKeyword|null  $keyword  The expected symbolic keyword
     * @param int|null         $years    The expected years
     * @param int|null         $months   The expected months
     * @param int|null         $days     The expected days
     */
    #[Test]
    #[DataProvider('ageProvider')]
    public function fromGedcomParsesTheGrammar(
        string $raw,
        ?AgeModifier $modifier,
        ?AgeKeyword $keyword,
        ?int $years,
        ?int $months,
        ?int $days,
    ): void {
        $age = AgeValue::fromGedcom($raw);

        self::assertSame($modifier, $age->modifier, 'modifier');
        self::assertSame($keyword, $age->keyword, 'keyword');
        self::assertSame($years, $age->years, 'years');
        self::assertSame($months, $age->months, 'months');
        self::assertSame($days, $age->days, 'days');
    }

    /**
     * The original raw text is preserved verbatim, including surrounding whitespace.
     */
    #[Test]
    public function fromGedcomPreservesTheRawText(): void
    {
        self::assertSame('  72y 3m  ', AgeValue::fromGedcom('  72y 3m  ')->raw);
    }

    /**
     * FamilyPersonAge exposes the parsed age, and NULL when no AGE value is present.
     */
    #[Test]
    public function familyPersonAgeExposesTheParsedAgeValue(): void
    {
        $age = new FamilyPersonAge();
        self::assertNull($age->getAgeValue());

        $age->setValue(FamilyPersonAgeInterface::TAG_AGE, '72y');
        $value = $age->getAgeValue();

        self::assertInstanceOf(AgeValue::class, $value);
        self::assertSame(72, $value->years);
    }

    /**
     * IndividualEventDetail exposes the parsed age, and NULL when no AGE value is present.
     */
    #[Test]
    public function individualEventDetailExposesTheParsedAgeValue(): void
    {
        $detail = new IndividualEventDetail();
        self::assertNull($detail->getAgeValue());

        $detail->setValue(IndividualEventDetailInterface::TAG_AGE, 'CHILD');
        $value = $detail->getAgeValue();

        self::assertInstanceOf(AgeValue::class, $value);
        self::assertSame(AgeKeyword::Child, $value->keyword);
    }
}
