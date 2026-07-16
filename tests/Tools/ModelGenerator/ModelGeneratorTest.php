<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Tools\ModelGenerator;

use MagicSunday\Gedcom\Schema\Cardinality;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\Schema\StructureDefinition;
use MagicSunday\Gedcom\Schema\Substructure;
use MagicSunday\Gedcom\Tools\ModelGenerator\ClassRenderer;
use MagicSunday\Gedcom\Tools\ModelGenerator\ClassSpec;
use MagicSunday\Gedcom\Tools\ModelGenerator\ModelGenerator;
use MagicSunday\Gedcom\Tools\ModelGenerator\PropertySpec;
use MagicSunday\Gedcom\Tools\ModelGenerator\TypeMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function dirname;
use function str_contains;
use function token_get_all;

use const TOKEN_PARSE;

/**
 * Tests the {@see ModelGenerator} end to end against the real vendored registry: generating the
 * GEDCOM 5.5.1 pointer source-citation structure produces a syntactically valid, house-style typed
 * class whose data shape (the cross-reference, `PAGE`, `QUAY`, the reused `Note` model and the
 * `$unknown` catch-all) matches what the hand-written `SourceCitation` models — proving the
 * schema-driven generation approach before the full roll-out.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(ModelGenerator::class)]
#[UsesClass(ClassRenderer::class)]
#[UsesClass(ClassSpec::class)]
#[UsesClass(PropertySpec::class)]
#[UsesClass(TypeMapper::class)]
#[UsesClass(RegistrySchemaLoader::class)]
#[UsesClass(Schema::class)]
#[UsesClass(GedcomVersion::class)]
#[UsesClass(StructureDefinition::class)]
#[UsesClass(Substructure::class)]
#[UsesClass(Cardinality::class)]
class ModelGeneratorTest extends TestCase
{
    /**
     * Generating the pointer source-citation structure yields a valid class carrying the modelled
     * data shape.
     */
    #[Test]
    public function itGeneratesThePointerSourceCitationDataShape(): void
    {
        $registry   = dirname(__DIR__, 3) . '/docs/spec/gedcom7-registries';
        $schema     = (new RegistrySchemaLoader($registry))->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/SOUR-XREF_SOUR');

        self::assertInstanceOf(StructureDefinition::class, $definition);

        $php = (new ModelGenerator())->generate(
            $definition,
            $schema,
            'MagicSunday\\Gedcom\\Model\\Substructure\\Source',
            'GeneratedSourceCitation',
            'A generated source citation.',
        );

        // Syntactically valid PHP (TOKEN_PARSE makes token_get_all raise a ParseError on invalid
        // input; a valid file tokenises to a non-empty list).
        self::assertNotEmpty(token_get_all($php, TOKEN_PARSE));

        // The pointer cross-reference, the leaf strings, the reused Note model and the safety net.
        self::assertStringContainsString('final readonly class GeneratedSourceCitation', $php);
        self::assertStringContainsString('public ?string $xref = null,', $php);
        self::assertStringContainsString('public ?string $page = null,', $php);
        self::assertStringContainsString('public ?string $quay = null,', $php);
        self::assertStringContainsString('public array $note = [],', $php);
        self::assertStringContainsString('@param list<Note>', $php);
        self::assertStringContainsString('public array $unknown = [],', $php);
        self::assertStringContainsString('use MagicSunday\\Gedcom\\Model\\Note;', $php);
        self::assertStringContainsString('use MagicSunday\\Gedcom\\ValueObject\\RawSubstructure;', $php);

        // DATA is a pure container (no payload of its own) → deferred to its own class, not emitted.
        self::assertFalse(str_contains($php, '$data'), 'DATA is a pure container substructure, deferred.');
    }

    /**
     * A leaf carrying a grammar value-object payload (`DATE` → `DateValue`) is emitted as that typed
     * property AND its `use` import — even though a date also bears substructures — so the generated
     * class references a defined type.
     */
    #[Test]
    public function itImportsAValueObjectLeaf(): void
    {
        // A date carries a value payload and its own TIME/PHRASE substructures; it must map to the
        // value object (by payload), not be deferred as a container.
        $date = new StructureDefinition(
            'urn:date',
            'DATE',
            'https://gedcom.io/terms/v7/type-Date#exact',
            null,
            ['TIME' => [new Substructure('urn:time', Cardinality::fromToken('{0:1}'))]],
        );

        // An address carries a string payload AND substructures; the mapper shapes it as an array,
        // so it must be deferred to its own class rather than mis-mapped to a scalar string.
        $addr = new StructureDefinition(
            'urn:addr',
            'ADDR',
            'http://www.w3.org/2001/XMLSchema#string',
            null,
            ['CITY' => [new Substructure('urn:city', Cardinality::fromToken('{0:1}'))]],
        );

        $parent = new StructureDefinition(
            'urn:parent',
            'FOO',
            null,
            null,
            [
                'DATE' => [new Substructure('urn:date', Cardinality::fromToken('{0:1}'))],
                'ADDR' => [new Substructure('urn:addr', Cardinality::fromToken('{0:1}'))],
            ],
        );

        $schema = new Schema(['urn:date' => $date, 'urn:addr' => $addr, 'urn:parent' => $parent]);

        $php = (new ModelGenerator())->generate(
            $parent,
            $schema,
            'MagicSunday\\Gedcom\\Model\\Substructure\\Common',
            'GeneratedFoo',
            'A generated container.',
        );

        self::assertNotEmpty(token_get_all($php, TOKEN_PARSE));
        self::assertStringContainsString('use MagicSunday\\Gedcom\\ValueObject\\DateValue;', $php);
        self::assertStringContainsString('public ?DateValue $date = null,', $php);
        self::assertFalse(
            str_contains($php, '$addr'),
            'A string payload that also bears substructures is deferred to its own class, not a scalar.',
        );
    }
}
