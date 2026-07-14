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
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\Schema\StructureDefinition;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\TypedModel\EventDetail;
use MagicSunday\Gedcom\TypedModel\IndividualRecord;
use MagicSunday\Gedcom\TypedModel\PersonalName;
use MagicSunday\Gedcom\TypedModel\SubmitterRecord;
use MagicSunday\Gedcom\ValueObject\AgeKeyword;
use MagicSunday\Gedcom\ValueObject\AgeModifier;
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\CalendarDate;
use MagicSunday\Gedcom\ValueObject\DateType;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
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
#[CoversClass(IndividualRecord::class)]
#[CoversClass(EventDetail::class)]
#[CoversClass(PersonalName::class)]
#[UsesClass(GedcomTreeReader::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(Reader::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(RegistrySchemaLoader::class)]
#[UsesClass(Schema::class)]
#[UsesClass(StructureDefinition::class)]
#[UsesClass(GedcomVersion::class)]
#[UsesClass(MappingException::class)]
#[UsesClass(DateValue::class)]
#[UsesClass(CalendarDate::class)]
#[UsesClass(DateType::class)]
#[UsesClass(PlaceValue::class)]
#[UsesClass(AgeValue::class)]
#[UsesClass(AgeModifier::class)]
#[UsesClass(AgeKeyword::class)]
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
        $record = $this->mapSubmitter(
            "0 @SUBM1@ SUBM\n1 NAME John Doe\n1 PHON 555-1234\n1 PHON 555-5678\n0 TRLR\n"
        );

        self::assertSame('SUBM1', $record->xref);
        self::assertSame('John Doe', $record->name);
        self::assertSame(['555-1234', '555-5678'], $record->phon);
    }

    /**
     * A nested event substructure is mapped into a typed EventDetail, and its DATE payload is
     * parsed into a typed DateValue value object through the registered custom type handler.
     */
    #[Test]
    public function mapsANestedEventWithATypedDateValueObject(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 BIRT\n2 DATE 1 JAN 2000\n0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/record-INDI');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, IndividualRecord::class);

        self::assertInstanceOf(IndividualRecord::class, $record);
        self::assertSame('I1', $record->xref);

        // BIRT is {0:M} in the schema, so it maps to a list of events.
        self::assertCount(1, $record->birt, 'a single BIRT maps to a one-element list');
        $birth = $record->birt[0];
        self::assertInstanceOf(EventDetail::class, $birth);
        self::assertInstanceOf(DateValue::class, $birth->date);
        self::assertSame('1 JAN 2000', $birth->date->raw);
        self::assertInstanceOf(CalendarDate::class, $birth->date->date);
        self::assertSame(2451545, $birth->date->date->toJulianDay(), 'the DATE payload parsed to its Julian day');
    }

    /**
     * A non-string DATE payload reaching the value-object handler (a mis-shaped node) fails loud
     * as a MappingException rather than being silently parsed as an empty date.
     */
    #[Test]
    public function failsLoudlyWhenADateLeafIsMisShaped(): void
    {
        $this->expectException(MappingException::class);

        JsonMapperFactory::create()->map(['date' => ['unexpected' => 'value']], EventDetail::class);
    }

    /**
     * A non-array, non-string PLAC payload reaching the place handler fails loud.
     */
    #[Test]
    public function failsLoudlyWhenAPlaceLeafIsMisShaped(): void
    {
        $this->expectException(MappingException::class);

        JsonMapperFactory::create()->map(['plac' => 42], EventDetail::class);
    }

    /**
     * A non-string AGE payload reaching the age handler fails loud.
     */
    #[Test]
    public function failsLoudlyWhenAnAgeLeafIsMisShaped(): void
    {
        $this->expectException(MappingException::class);

        JsonMapperFactory::create()->map(['age' => ['unexpected' => 'value']], EventDetail::class);
    }

    /**
     * A PLAC with no FORM substructure maps to a PlaceValue whose form is null while the value is
     * still split into its jurisdiction levels.
     */
    #[Test]
    public function mapsAPlaceWithoutAFormAsFormNull(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston, Massachusetts\n0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/record-INDI');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, IndividualRecord::class);

        self::assertInstanceOf(IndividualRecord::class, $record);
        $place = $record->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertNull($place->form, 'a PLAC without a FORM substructure has a null form');
        self::assertSame(['Boston', 'Massachusetts'], $place->levels);
    }

    /**
     * mapRecord resolves the record definition from the node's tag, so the caller need not supply
     * it.
     */
    #[Test]
    public function mapRecordResolvesTheDefinitionFromTheTag(): void
    {
        $stream = (new StreamFactory())->createStream("0 @SUBM1@ SUBM\n1 NAME John Doe\n0 TRLR\n");
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, SubmitterRecord::class);

        self::assertSame('SUBM1', $record->xref);
        self::assertSame('John Doe', $record->name);
    }

    /**
     * mapRecord fails with a MappingException when the node is not at level 0, so a malformed
     * deeper line cannot be mapped as a record.
     */
    #[Test]
    public function mapRecordThrowsWhenTheNodeIsNotAtLevelZero(): void
    {
        $stream = (new StreamFactory())->createStream("1 @I1@ INDI\n0 TRLR\n");
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertSame(1, $node->level, 'the malformed record sits at level 1');

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $this->expectException(MappingException::class);

        (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, IndividualRecord::class);
    }

    /**
     * mapRecord fails with a MappingException when the node's tag is not a top-level record.
     */
    #[Test]
    public function mapRecordThrowsWhenTheTagIsNotARecord(): void
    {
        $stream = (new StreamFactory())->createStream("0 PHON 555\n0 TRLR\n");
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $this->expectException(MappingException::class);

        (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, SubmitterRecord::class);
    }

    /**
     * An event maps its DATE, PLAC and AGE substructures into their typed value objects — the
     * PLAC carries both a value and a FORM substructure, so its shaped node is threaded through
     * the value object with the form hierarchy.
     */
    #[Test]
    public function mapsTheEventDatePlaceAndAgeValueObjects(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 BIRT\n"
            . "2 DATE 1 JAN 2000\n"
            . "2 PLAC Boston, Massachusetts, USA\n3 FORM City, State, Country\n"
            . "2 AGE 0y\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/record-INDI');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, IndividualRecord::class);

        self::assertInstanceOf(IndividualRecord::class, $record);
        self::assertCount(1, $record->birt);
        $birth = $record->birt[0];

        self::assertInstanceOf(DateValue::class, $birth->date);

        self::assertInstanceOf(PlaceValue::class, $birth->plac);
        self::assertSame('Boston, Massachusetts, USA', $birth->plac->raw);
        self::assertSame(['Boston', 'Massachusetts', 'USA'], $birth->plac->levels);
        self::assertSame('City, State, Country', $birth->plac->form, 'the PLAC FORM substructure is threaded through');

        self::assertInstanceOf(AgeValue::class, $birth->age);
        self::assertSame(0, $birth->age->years);
    }

    /**
     * A richer individual maps its repeatable NAME substructures (each a nested structure with a
     * value and GIVN/SURN parts), its single SEX value, and its birth event with a typed date.
     */
    #[Test]
    public function mapsAnIndividualWithNamesSexAndEvents(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n"
            . "1 NAME John /Doe/\n2 GIVN John\n2 SURN Doe\n"
            . "1 NAME Johnny /Doe/\n"
            . "1 SEX M\n"
            . "1 BIRT\n2 DATE 1 JAN 2000\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/record-INDI');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, IndividualRecord::class);

        self::assertInstanceOf(IndividualRecord::class, $record);
        self::assertSame('M', $record->sex);

        self::assertCount(2, $record->name, 'both NAME lines map to a list of names');
        $primary = $record->name[0];
        self::assertInstanceOf(PersonalName::class, $primary);
        self::assertSame('John /Doe/', $primary->value, 'the NAME value string is preserved');
        self::assertSame('John', $primary->givn, 'the GIVN name-part substructure is mapped');
        self::assertSame('Doe', $primary->surn, 'the SURN name-part substructure is mapped');
        $secondary = $record->name[1];
        self::assertInstanceOf(PersonalName::class, $secondary);
        self::assertSame('Johnny /Doe/', $secondary->value);
        self::assertNull($secondary->givn, 'a NAME with no GIVN part maps to null');
        self::assertNull($secondary->surn, 'a NAME with no SURN part maps to null');

        self::assertCount(1, $record->birt, 'the single BIRT maps to a one-element list');
        self::assertInstanceOf(DateValue::class, $record->birt[0]->date);
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
