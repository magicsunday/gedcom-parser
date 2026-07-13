<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Encoding;

use MagicSunday\Gedcom\Encoding\AnselDecoder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function chr;
use function mb_check_encoding;

/**
 * Tests the ANSEL (ANSI Z39.47) to UTF-8 decoder.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(AnselDecoder::class)]
class AnselDecoderTest extends TestCase
{
    /**
     * ASCII bytes pass through unchanged.
     */
    #[Test]
    public function passesAsciiThrough(): void
    {
        self::assertSame('0 @I1@ INDI', AnselDecoder::decode('0 @I1@ INDI'));
    }

    /**
     * Each ANSEL graphic base byte decodes to its Z39.47 Unicode scalar.
     *
     * @param int    $byte     the ANSEL byte
     * @param string $expected the expected UTF-8 character
     */
    #[DataProvider('baseCharacterProvider')]
    #[Test]
    public function decodesBaseCharacters(int $byte, string $expected): void
    {
        self::assertSame($expected, AnselDecoder::decode(chr($byte)));
    }

    /**
     * @return array<string, array{0: int, 1: string}>
     */
    public static function baseCharacterProvider(): array
    {
        return [
            'slash L upper'  => [0xA1, 'Ł'],
            'slash O upper'  => [0xA2, 'Ø'],
            'thorn upper'    => [0xA4, 'Þ'],
            'ligature AE'    => [0xA5, 'Æ'],
            'ligature OE'    => [0xA6, 'Œ'],
            'eth'            => [0xBA, 'ð'],
            'pound'          => [0xB9, '£'],
            'copyright'      => [0xC3, '©'],
            'inverted quest' => [0xC5, '¿'],
            'es zet'         => [0xCF, 'ß'],
        ];
    }

    /**
     * An ANSEL combining diacritic precedes its base; it is reordered after the base and
     * NFC-composed into a precomposed character.
     */
    #[Test]
    public function reordersCombiningMarkAfterBaseAndComposes(): void
    {
        // ANSEL: 0xE2 (acute) then 'e' -> Unicode "e" + U+0301 -> NFC "é".
        self::assertSame('é', AnselDecoder::decode(chr(0xE2) . 'e'));
        // 0xE8 (diaeresis) then 'u' -> "ü".
        self::assertSame('ü', AnselDecoder::decode(chr(0xE8) . 'u'));
    }

    /**
     * Stacked combining marks (different combining classes) both apply to the base.
     */
    #[Test]
    public function appliesStackedCombiningMarks(): void
    {
        // 0xE2 (acute, class 230) + 0xF0 (cedilla, class 202) then 'x' (no precomposed form,
        // so the base letter survives and both marks remain).
        $decoded = AnselDecoder::decode(chr(0xE2) . chr(0xF0) . 'x');

        self::assertTrue(mb_check_encoding($decoded, 'UTF-8'));
        self::assertStringContainsString('x', $decoded);
        self::assertStringContainsString("\u{0301}", $decoded);
        self::assertStringContainsString("\u{0327}", $decoded);
    }

    /**
     * The MARC-8/ANSEL control region (non-sort delimiters, fill) produces no output.
     */
    #[Test]
    public function stripsControlRegion(): void
    {
        self::assertSame('AB', AnselDecoder::decode('A' . chr(0x8D) . chr(0x8E) . 'B'));
    }

    /**
     * An undefined high byte becomes the Unicode replacement character, never a raw byte,
     * so the output is always valid UTF-8.
     */
    #[Test]
    public function replacesUndefinedBytesAndStaysValidUtf8(): void
    {
        $decoded = AnselDecoder::decode(chr(0xD0) . chr(0xFF));

        self::assertSame("\u{FFFD}\u{FFFD}", $decoded);
        self::assertTrue(mb_check_encoding($decoded, 'UTF-8'));
    }

    /**
     * A trailing combining mark with no following base is applied to a space so the output
     * stays valid UTF-8 rather than dangling.
     */
    #[Test]
    public function appliesDanglingTrailingMarkToASpace(): void
    {
        $decoded = AnselDecoder::decode('a' . chr(0xE2));

        self::assertTrue(mb_check_encoding($decoded, 'UTF-8'));
        self::assertStringStartsWith('a', $decoded);
        self::assertStringContainsString("\u{0301}", $decoded);
    }
}
