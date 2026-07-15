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
     * @return array<string, array{0: string, 1: AgeModifier|null, 2: AgeKeyword|null, 3: int|null, 4: int|null, 5: int|null, 6: int|null}>
     */
    public static function ageProvider(): array
    {
        return [
            // label                     raw            modifier                  keyword               y     m     w     d
            'full duration'    => ['72y 3m 2d', null, null, 72, 3, null, 2],
            'years only'       => ['72y', null, null, 72, null, null, null],
            'months only'      => ['3m', null, null, null, 3, null, null],
            'days only'        => ['15d', null, null, null, null, null, 15],
            'years and months' => ['1y 6m', null, null, 1, 6, null, null],
            // The GEDCOM 7.0 weeks unit (5.5.1 has none), alone and within the ordered duration.
            'weeks only'            => ['8w', null, null, null, null, 8, null],
            'weeks with days'       => ['6w 3d', null, null, null, null, 6, 3],
            'full 7.0 duration'     => ['1y 2m 3w 4d', null, null, 1, 2, 3, 4],
            'less than years'       => ['< 8y', AgeModifier::LessThan, null, 8, null, null, null],
            'greater than years'    => ['> 99y', AgeModifier::GreaterThan, null, 99, null, null, null],
            'less than no space'    => ['<1y', AgeModifier::LessThan, null, 1, null, null, null],
            'child keyword'         => ['CHILD', null, AgeKeyword::Child, null, null, null, null],
            'infant keyword'        => ['INFANT', null, AgeKeyword::Infant, null, null, null, null],
            'stillborn keyword'     => ['STILLBORN', null, AgeKeyword::Stillborn, null, null, null, null],
            'lowercase keyword'     => ['child', null, AgeKeyword::Child, null, null, null, null],
            'uppercase unit labels' => ['5Y 2M', null, null, 5, 2, null, null],
            'empty'                 => ['', null, null, null, null, null, null],
            // Non-conformant input is left unparsed (all NULL) rather than yielding a wrong parse.
            'reversed order'        => ['1d 2m 3y', null, null, null, null, null, null],
            'weeks after days'      => ['3d 8w', null, null, null, null, null, null],
            'keyword with trailing' => ['CHILD 4y', null, null, null, null, null, null],
            'garbage between units' => ['72y 3 minutes 2d', null, null, null, null, null, null],
            'label inside a word'   => ['aged 5 years, 60y', null, null, null, null, null, null],
            'space within a pair'   => ['8 y', null, null, null, null, null, null],
            // A relational qualifier without a valid operand is meaningless; drop it entirely.
            'modifier only'         => ['<', null, null, null, null, null, null],
            'modifier then garbage' => ['> garbage', null, null, null, null, null, null],
        ];
    }

    /**
     * @param string           $raw      The raw AGE value
     * @param AgeModifier|null $modifier The expected relational qualifier
     * @param AgeKeyword|null  $keyword  The expected symbolic keyword
     * @param int|null         $years    The expected years
     * @param int|null         $months   The expected months
     * @param int|null         $weeks    The expected weeks
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
        ?int $weeks,
        ?int $days,
    ): void {
        $age = AgeValue::fromGedcom($raw);

        self::assertSame($modifier, $age->modifier, 'modifier');
        self::assertSame($keyword, $age->keyword, 'keyword');
        self::assertSame($years, $age->years, 'years');
        self::assertSame($months, $age->months, 'months');
        self::assertSame($weeks, $age->weeks, 'weeks');
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

        $empty = new FamilyPersonAge();
        $empty->setValue(FamilyPersonAgeInterface::TAG_AGE, '  ');
        self::assertNull($empty->getAgeValue(), 'an empty AGE is treated as absent');

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

        $empty = new IndividualEventDetail();
        $empty->setValue(IndividualEventDetailInterface::TAG_AGE, '  ');
        self::assertNull($empty->getAgeValue(), 'an empty AGE is treated as absent');

        $detail->setValue(IndividualEventDetailInterface::TAG_AGE, 'CHILD');
        $value = $detail->getAgeValue();

        self::assertInstanceOf(AgeValue::class, $value);
        self::assertSame(AgeKeyword::Child, $value->keyword);
    }

    /**
     * A value-less AGE carried solely by a GEDCOM 7.0 PHRASE substructure records only the phrase,
     * with no duration, keyword or modifier.
     */
    #[Test]
    public function fromGedcomBuildsAPhraseOnlyAgeFromAValueLessAge(): void
    {
        $value = AgeValue::fromGedcom('', 'a young child');

        self::assertNull($value->modifier);
        self::assertNull($value->keyword);
        self::assertNull($value->years);
        self::assertNull($value->months);
        self::assertNull($value->days);
        self::assertSame('a young child', $value->phrase);
    }

    /**
     * A valued AGE that also carries an explicit GEDCOM 7.0 PHRASE keeps its parsed parts and
     * records the phrase alongside.
     */
    #[Test]
    public function fromGedcomAttachesAnExplicitPhraseToAValuedAge(): void
    {
        $value = AgeValue::fromGedcom('72y 3m 2d', 'at the wedding');

        self::assertSame(72, $value->years);
        self::assertSame(3, $value->months);
        self::assertSame(2, $value->days);
        self::assertSame('at the wedding', $value->phrase);
    }

    /**
     * An empty or whitespace-only explicit phrase is treated as absent.
     */
    #[Test]
    public function fromGedcomTreatsAnEmptyExplicitAgePhraseAsAbsent(): void
    {
        $value = AgeValue::fromGedcom('CHILD', '   ');

        self::assertSame(AgeKeyword::Child, $value->keyword);
        self::assertNull($value->phrase);
    }
}
