<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Schema;

use MagicSunday\Gedcom\Schema\Cardinality;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests parsing of a GEDCOM registry cardinality token into a typed value object.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Cardinality::class)]
class CardinalityTest extends TestCase
{
    /**
     * Data provider for the cardinality grammar.
     *
     * @return array<string, array{0: string, 1: int, 2: int|null, 3: bool, 4: bool}>
     */
    public static function cardinalityProvider(): array
    {
        return [
            // token       minimum  maximum  required  collection
            'optional single' => ['{0:1}', 0, 1, false, false],
            'required single' => ['{1:1}', 1, 1, true, false],
            'optional many'   => ['{0:M}', 0, null, false, true],
            'required many'   => ['{1:M}', 1, null, true, true],
            'bounded many'    => ['{0:3}', 0, 3, false, true],
            'with whitespace' => [' {0:1} ', 0, 1, false, false],
        ];
    }

    /**
     * @param string   $token      The raw cardinality token
     * @param int      $minimum    The expected minimum occurrence
     * @param int|null $maximum    The expected maximum occurrence, or NULL for unbounded
     * @param bool     $required   Whether the substructure is required
     * @param bool     $collection Whether more than one occurrence is allowed
     */
    #[Test]
    #[DataProvider('cardinalityProvider')]
    public function fromTokenParsesTheGrammar(
        string $token,
        int $minimum,
        ?int $maximum,
        bool $required,
        bool $collection,
    ): void {
        $cardinality = Cardinality::fromToken($token);

        self::assertSame($minimum, $cardinality->minimum, 'minimum');
        self::assertSame($maximum, $cardinality->maximum, 'maximum');
        self::assertSame($required, $cardinality->isRequired(), 'isRequired');
        self::assertSame($collection, $cardinality->isCollection(), 'isCollection');
    }

    /**
     * A malformed token is rejected with a dedicated exception rather than silently parsed.
     */
    #[Test]
    public function fromTokenRejectsAMalformedToken(): void
    {
        $this->expectException(\MagicSunday\Gedcom\Exception\InvalidCardinalityException::class);

        Cardinality::fromToken('0..1');
    }
}
