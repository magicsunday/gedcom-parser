<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\ValueObject;

use MagicSunday\Gedcom\ValueObject\PlaceValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests parsing of the GEDCOM PLACE_NAME hierarchy into a typed value object.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(PlaceValue::class)]
class PlaceValueTest extends TestCase
{
    /**
     * Data provider for the comma-separated hierarchy split.
     *
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function levelsProvider(): array
    {
        return [
            'full hierarchy'   => ['Cove, Cache, Utah, USA', ['Cove', 'Cache', 'Utah', 'USA']],
            'single level'     => ['USA', ['USA']],
            'leading empties'  => [', , Utah, USA', ['', '', 'Utah', 'USA']],
            'trailing empty'   => ['Berlin, ', ['Berlin', '']],
            'extra whitespace' => ['  Berlin ,  Germany  ', ['Berlin', 'Germany']],
            'empty place'      => ['', []],
        ];
    }

    /**
     * @param string       $place    The raw PLACE_NAME value.
     * @param list<string> $expected The expected trimmed, positional levels.
     */
    #[Test]
    #[DataProvider('levelsProvider')]
    public function fromGedcomSplitsTheHierarchy(string $place, array $expected): void
    {
        self::assertSame($expected, PlaceValue::fromGedcom($place)->levels);
    }

    /**
     * The original raw text is preserved verbatim.
     */
    #[Test]
    public function fromGedcomPreservesTheRawText(): void
    {
        self::assertSame('  Cove, Cache  ', PlaceValue::fromGedcom('  Cove, Cache  ')->raw);
    }

    /**
     * A FORM is normalised: an empty or whitespace-only value becomes NULL, and surrounding
     * whitespace is trimmed off a non-empty value.
     */
    #[Test]
    public function fromGedcomNormalisesTheForm(): void
    {
        self::assertNull(PlaceValue::fromGedcom('Berlin', '   ')->form);
        self::assertSame('City', PlaceValue::fromGedcom('Berlin', '  City  ')->form);
    }

    /**
     * With a FORM, the labels map onto the hierarchy levels by position.
     */
    #[Test]
    public function mappedZipsFormLabelsToLevels(): void
    {
        $place = PlaceValue::fromGedcom('Cove, Cache, Utah, USA', 'City, County, State, Country');

        self::assertSame(
            ['City' => 'Cove', 'County' => 'Cache', 'State' => 'Utah', 'Country' => 'USA'],
            $place->mapped(),
        );
    }

    /**
     * Without a FORM, the mapping is empty.
     */
    #[Test]
    public function mappedIsEmptyWithoutAForm(): void
    {
        self::assertSame([], PlaceValue::fromGedcom('Cove, Cache')->mapped());
    }

    /**
     * An empty FORM label (a padded, unnamed jurisdiction) is skipped while the aligned
     * neighbours still map.
     */
    #[Test]
    public function mappedSkipsAnEmptyFormLabel(): void
    {
        $place = PlaceValue::fromGedcom('Cove, Cache, Utah', 'City, , State');

        self::assertSame(['City' => 'Cove', 'State' => 'Utah'], $place->mapped());
    }

    /**
     * When the FORM and the place have a different number of positions the mapping cannot be
     * trusted (it would misalign labels), so no map is produced.
     */
    #[Test]
    public function mappedIsEmptyWhenFormAndPlaceCountsDiffer(): void
    {
        // The place omits the County comma, so `County` would wrongly bind to `Maryland`.
        $place = PlaceValue::fromGedcom('Baltimore, Maryland, USA', 'City, County, State, Country');

        self::assertSame([], $place->mapped());
    }

    /**
     * A FORM that repeats a jurisdiction label cannot be represented unambiguously as a map.
     */
    #[Test]
    public function mappedIsEmptyOnDuplicateFormLabels(): void
    {
        $place = PlaceValue::fromGedcom('A, B', 'Area, Area');

        self::assertSame([], $place->mapped());
    }
}
