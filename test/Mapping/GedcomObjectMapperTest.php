<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Mapping;

use MagicSunday\Gedcom\Exception\MappingException;
use MagicSunday\Gedcom\Mapping\GedcomObjectMapper;
use MagicSunday\Gedcom\Mapping\JsonMapperFactory;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\StructureDefinition;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\TypedModel\SubmitterRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * End-to-end test of the schema-driven mapping: a parsed GEDCOM tree is mapped, through the
 * registry schema and JsonMapper, onto an immutable typed record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomObjectMapper::class)]
#[CoversClass(JsonMapperFactory::class)]
#[CoversClass(SubmitterRecord::class)]
#[UsesClass(GedcomTreeReader::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(Reader::class)]
#[UsesClass(RegistrySchemaLoader::class)]
class GedcomObjectMapperTest extends TestCase
{
    /**
     * A submitter record with a single required NAME and repeatable PHON substructures is mapped
     * onto the typed SubmitterRecord: the record identifier becomes the xref, the single NAME its
     * name, and the collection of PHON values a list.
     */
    #[Test]
    public function mapsASubmitterRecordOntoTheTypedModel(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @SUBM1@ SUBM\n1 NAME John Doe\n1 PHON 555-1234\n1 PHON 555-5678\n0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/record-SUBM');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        $mapper = new GedcomObjectMapper($schema, JsonMapperFactory::create());
        $record = $mapper->map($node, $definition, SubmitterRecord::class);

        self::assertInstanceOf(SubmitterRecord::class, $record);
        self::assertSame('SUBM1', $record->xref);
        self::assertSame('John Doe', $record->name);
        self::assertSame(['555-1234', '555-5678'], $record->phon);
    }

    /**
     * A line whose tag is not a permitted substructure is silently ignored rather than breaking
     * the mapping.
     */
    #[Test]
    public function ignoresATagThatIsNotAPermittedSubstructure(): void
    {
        $record = $this->mapSubmitter("0 @SUBM1@ SUBM\n1 NAME John Doe\n1 ZZZZ ignored\n0 TRLR\n");

        self::assertSame('John Doe', $record->name);
        self::assertSame([], $record->phon, 'the unknown tag does not appear as data');
    }

    /**
     * A substructure at a skipped level (more than one below its parent) is dropped rather than
     * mis-attributed to the record.
     */
    #[Test]
    public function dropsASubstructureAtASkippedLevel(): void
    {
        // The PHON sits at level 2 directly under the level-0 record, skipping level 1.
        $record = $this->mapSubmitter("0 @SUBM1@ SUBM\n2 PHON 555-9999\n1 NAME John Doe\n0 TRLR\n");

        self::assertSame('John Doe', $record->name);
        self::assertSame([], $record->phon, 'the level-skipped PHON is not attributed to the submitter');
    }

    /**
     * A failure inside the underlying mapper — here a non-nullable name receiving a null payload,
     * which surfaces as a TypeError rather than a mapper exception — is wrapped in the library's
     * own MappingException, so every mapping failure stays within the shared exception group.
     */
    #[Test]
    public function wrapsAMapperFailureInAMappingException(): void
    {
        $this->expectException(MappingException::class);

        // The NAME line carries no value, so the required string name is null at construction.
        $this->mapSubmitter("0 @SUBM1@ SUBM\n1 NAME\n0 TRLR\n");
    }

    /**
     * Maps a submitter record from an in-memory GEDCOM string onto the typed model.
     */
    private function mapSubmitter(string $gedcom): SubmitterRecord
    {
        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/record-SUBM');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        return (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, SubmitterRecord::class);
    }
}
