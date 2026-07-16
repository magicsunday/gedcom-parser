<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Tools\ModelGenerator;

use MagicSunday\Gedcom\Model\ExactDate;
use MagicSunday\Gedcom\Schema\Cardinality;
use MagicSunday\Gedcom\Tools\ModelGenerator\PropertySpec;
use MagicSunday\Gedcom\Tools\ModelGenerator\TypeMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function strtolower;

/**
 * Tests that the {@see TypeMapper} maps a leaf substructure's payload and cardinality onto the
 * correct PHP and PHPDoc types: a pointer payload becomes the cross-reference string, the known
 * grammar value objects map to their classes, an enumeration maps to a tolerant string, and the
 * cardinality decides between a nullable single value and a `list<>`.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(TypeMapper::class)]
#[UsesClass(PropertySpec::class)]
#[UsesClass(Cardinality::class)]
class TypeMapperTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string|null, 2: string, 3: string, 4: string, 5: string, 6: string|null}>
     */
    public static function leafProvider(): array
    {
        $date    = 'https://gedcom.io/terms/v7/type-Date';
        $exact   = 'https://gedcom.io/terms/v7/type-Date#exact';
        $place   = 'https://gedcom.io/terms/v5.5.1/type-PLACE_NAME';
        $age     = 'https://gedcom.io/terms/v7/type-Age';
        $v7place = 'https://gedcom.io/terms/v7/type-List#Text';
        $enum    = 'https://gedcom.io/terms/v7/type-Enum';
        $list    = 'https://gedcom.io/terms/v7/type-List-Enum';
        $vo      = 'MagicSunday\\Gedcom\\ValueObject\\';

        return [
            // tag,   payload,   cardinality, phpType,      docType,          default, import
            'single string'    => ['PAGE', 'http://www.w3.org/2001/XMLSchema#string', '{0:1}', '?string', 'string|null', 'null', null],
            'single enum'      => ['QUAY', $enum, '{0:1}', '?string', 'string|null', 'null', null],
            'single list-enum' => ['RESN', $list, '{0:1}', '?string', 'string|null', 'null', null],
            'single date'      => ['DATE', $date, '{0:1}', '?DateValue', 'DateValue|null', 'null', $vo . 'DateValue'],
            'exact date'       => ['DATE', $exact, '{0:1}', '?ExactDate', 'ExactDate|null', 'null', ExactDate::class],
            'single place'     => ['PLAC', $place, '{0:1}', '?PlaceValue', 'PlaceValue|null', 'null', $vo . 'PlaceValue'],
            'v7 place by tag'  => ['PLAC', $v7place, '{0:1}', '?PlaceValue', 'PlaceValue|null', 'null', $vo . 'PlaceValue'],
            'single age'       => ['AGE', $age, '{0:1}', '?AgeValue', 'AgeValue|null', 'null', $vo . 'AgeValue'],
            'pointer single'   => ['SUBM', '@<https://gedcom.io/terms/v7/record-SUBM>@', '{0:1}', '?string', 'string|null', 'null', null],
            'null payload'     => ['TEXT', null, '{0:1}', '?string', 'string|null', 'null', null],
            'empty payload'    => ['TEXT', '', '{0:1}', '?string', 'string|null', 'null', null],
            'unknown type'     => ['FOO', 'https://gedcom.io/terms/v7/type-Weird', '{0:1}', '?string', 'string|null', 'null', null],
            'list string'      => ['REFN', 'http://www.w3.org/2001/XMLSchema#string', '{0:M}', 'array', 'list<string>', '[]', null],
            'list date'        => ['DATE', $date, '{0:M}', 'array', 'list<DateValue>', '[]', $vo . 'DateValue'],
        ];
    }

    /**
     * A leaf substructure maps to the expected PHP type, PHPDoc type, default and import per its
     * payload and cardinality.
     */
    #[DataProvider('leafProvider')]
    #[Test]
    public function itMapsALeafToItsTypedProperty(
        string $tag,
        ?string $payload,
        string $cardinalityToken,
        string $phpType,
        string $docType,
        string $default,
        ?string $import,
    ): void {
        $property = (new TypeMapper())->forLeaf($tag, $payload, Cardinality::fromToken($cardinalityToken));

        self::assertSame(strtolower($tag), $property->name);
        self::assertSame($phpType, $property->phpType);
        self::assertSame($docType, $property->docType);
        self::assertSame($default, $property->default);
        self::assertSame($import, $property->import);
    }
}
