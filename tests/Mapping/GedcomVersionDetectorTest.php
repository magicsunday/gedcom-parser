<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Mapping;

use MagicSunday\Gedcom\Mapping\GedcomVersionDetector;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the GEDCOM version is resolved from a header's GEDC.VERS line, that any 7.x patch
 * level maps to GEDCOM 7.0, and that an absent header, GEDC, VERS or an unrecognised value falls
 * back to the 5.5.1 baseline.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomVersionDetector::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(GedcomVersion::class)]
class GedcomVersionDetectorTest extends TestCase
{
    /**
     * A header's GEDC.VERS value resolves to the matching version: the exact 5.5.1 and 7.0 strings,
     * any 7.x patch level as 7.0, surrounding whitespace tolerated, and an unrecognised or older
     * value falling back to 5.5.1.
     *
     * @param string        $versValue The raw GEDC.VERS value.
     * @param GedcomVersion $expected  The expected resolved version.
     */
    #[Test]
    #[DataProvider('versionValueProvider')]
    public function detectsTheVersionFromTheHeaderVersLine(string $versValue, GedcomVersion $expected): void
    {
        $header = $this->header($versValue);

        self::assertSame($expected, (new GedcomVersionDetector())->detect($header));
    }

    /**
     * @return array<string, array{0: string, 1: GedcomVersion}>
     */
    public static function versionValueProvider(): array
    {
        return [
            'exact 5.5.1'          => ['5.5.1', GedcomVersion::V551],
            'exact 7.0'            => ['7.0', GedcomVersion::V70],
            '7.0 patch level'      => ['7.0.14', GedcomVersion::V70],
            'future 7.x minor'     => ['7.1', GedcomVersion::V70],
            'surrounding spaces'   => ['  7.0  ', GedcomVersion::V70],
            'older 5.5'            => ['5.5', GedcomVersion::V551],
            'digits without a dot' => ['70', GedcomVersion::V551],
            'non-numeric 7 prefix' => ['7-draft', GedcomVersion::V551],
            'unrecognised value'   => ['nonsense', GedcomVersion::V551],
        ];
    }

    /**
     * A missing header falls back to the 5.5.1 baseline.
     */
    #[Test]
    public function fallsBackToTheBaselineWhenTheHeaderIsAbsent(): void
    {
        self::assertSame(GedcomVersion::V551, (new GedcomVersionDetector())->detect(null));
    }

    /**
     * A header without a GEDC substructure falls back to the 5.5.1 baseline.
     */
    #[Test]
    public function fallsBackToTheBaselineWhenGedcIsAbsent(): void
    {
        $header = new GedcomNode(0, 'HEAD', null, null, null);

        self::assertSame(GedcomVersion::V551, (new GedcomVersionDetector())->detect($header));
    }

    /**
     * A GEDC substructure without a VERS line falls back to the 5.5.1 baseline.
     */
    #[Test]
    public function fallsBackToTheBaselineWhenVersIsAbsent(): void
    {
        $gedc   = new GedcomNode(1, 'GEDC', null, null, null);
        $header = new GedcomNode(0, 'HEAD', null, null, null, [$gedc]);

        self::assertSame(GedcomVersion::V551, (new GedcomVersionDetector())->detect($header));
    }

    /**
     * A value-less VERS line falls back to the 5.5.1 baseline.
     */
    #[Test]
    public function fallsBackToTheBaselineWhenVersHasNoValue(): void
    {
        $vers   = new GedcomNode(2, 'VERS', null, null, null);
        $gedc   = new GedcomNode(1, 'GEDC', null, null, null, [$vers]);
        $header = new GedcomNode(0, 'HEAD', null, null, null, [$gedc]);

        self::assertSame(GedcomVersion::V551, (new GedcomVersionDetector())->detect($header));
    }

    /**
     * Builds a minimal HEAD record node carrying a GEDC.VERS line with the given value.
     *
     * @param string $versValue The GEDC.VERS value to embed.
     *
     * @return GedcomNode The header node.
     */
    private function header(string $versValue): GedcomNode
    {
        $vers = new GedcomNode(2, 'VERS', null, null, $versValue);
        $gedc = new GedcomNode(1, 'GEDC', null, null, null, [$vers]);

        return new GedcomNode(0, 'HEAD', null, null, null, [$gedc]);
    }
}
