<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Enumeration;

use MagicSunday\Gedcom\Enumeration\Sex;
use MagicSunday\Gedcom\Model\IndividualRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the SEX enumeration constant holder: its constants carry the raw single-letter spec tokens
 * (named after their meaning), its value list stays in sync with the vendored registry, and the
 * carrying model field remains a tolerant string.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Sex::class)]
#[UsesClass(IndividualRecord::class)]
class SexTest extends TestCase
{
    /**
     * Provides each meaning-named constant and the raw single-letter token it must carry.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function constantProvider(): array
    {
        return [
            'female'       => ['F', Sex::FEMALE],
            'male'         => ['M', Sex::MALE],
            'undetermined' => ['U', Sex::UNDETERMINED],
            'other'        => ['X', Sex::OTHER],
        ];
    }

    /**
     * Each meaning-named constant carries its raw single-letter GEDCOM token. Unlike the sets whose
     * constant name is the token, SEX names the constants after their meaning, so the mapping is
     * asserted explicitly.
     *
     * @param string $token    The raw single-letter token.
     * @param string $constant The constant value.
     */
    #[DataProvider('constantProvider')]
    #[Test]
    public function eachConstantCarriesItsRawToken(string $token, string $constant): void
    {
        self::assertSame($token, $constant);
    }

    /**
     * The holder's value list matches the vendored `enumset-SEX` registry exactly.
     */
    #[Test]
    public function theValueListMatchesTheRegistry(): void
    {
        EnumerationRegistry::assertMatchesRegistry('SEX', Sex::values());
    }

    /**
     * The carrying model field stays a tolerant string: a known value round-trips as its constant,
     * and an unlisted or extension value is preserved rather than rejected.
     */
    #[Test]
    public function theModelFieldRemainsATolerantString(): void
    {
        self::assertSame(Sex::MALE, (new IndividualRecord('I1', [], Sex::MALE))->sex);

        self::assertNotContains('_CUSTOM', Sex::values(), 'an extension value is not a known standard value');
        self::assertSame('_CUSTOM', (new IndividualRecord('I1', [], '_CUSTOM'))->sex, 'an extension value is preserved on the model');
    }
}
