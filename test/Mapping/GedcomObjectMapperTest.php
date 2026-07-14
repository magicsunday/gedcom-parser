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
use MagicSunday\Gedcom\TypedModel\ChildToFamilyLink;
use MagicSunday\Gedcom\TypedModel\EventDetail;
use MagicSunday\Gedcom\TypedModel\FamilyRecord;
use MagicSunday\Gedcom\TypedModel\IndividualRecord;
use MagicSunday\Gedcom\TypedModel\PersonalName;
use MagicSunday\Gedcom\TypedModel\SourceRecord;
use MagicSunday\Gedcom\TypedModel\SpouseToFamilyLink;
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
#[CoversClass(FamilyRecord::class)]
#[CoversClass(ChildToFamilyLink::class)]
#[CoversClass(SpouseToFamilyLink::class)]
#[CoversClass(SourceRecord::class)]
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
     * A scalar non-string DATE payload reaching the value-object handler (a mis-shaped node) fails
     * loud as a MappingException. A shaped array is NOT a mis-shape — a GEDCOM 7.0 DATE carries
     * PHRASE/TIME substructures, so its payload legitimately arrives as an array (see
     * {@see mapsAGedcom7EventWhoseDateAndAgeCarrySubstructures}).
     */
    #[Test]
    public function failsLoudlyWhenADateLeafIsMisShaped(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessageMatches('/Expected a string or shaped DATE payload, got/');

        JsonMapperFactory::create()->map(['date' => 42], EventDetail::class);
    }

    /**
     * A scalar non-string, non-array PLAC payload reaching the place handler fails loud.
     */
    #[Test]
    public function failsLoudlyWhenAPlaceLeafIsMisShaped(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessageMatches('/Expected a string or shaped PLAC payload, got/');

        JsonMapperFactory::create()->map(['plac' => 42], EventDetail::class);
    }

    /**
     * A scalar non-string AGE payload reaching the age handler fails loud.
     */
    #[Test]
    public function failsLoudlyWhenAnAgeLeafIsMisShaped(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessageMatches('/Expected a string or shaped AGE payload, got/');

        JsonMapperFactory::create()->map(['age' => 42], EventDetail::class);
    }

    /**
     * A shaped leaf whose `value` key is present but not a string is a mis-shape (distinct from a
     * value-less leaf, which resolves to the empty string) and fails loud rather than silently
     * coercing the payload away.
     */
    #[Test]
    public function failsLoudlyWhenAShapedLeafValueIsNotAString(): void
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessageMatches('/Expected a string PLAC value, got array/');

        JsonMapperFactory::create()->map(['plac' => ['value' => ['nested']]], EventDetail::class);
    }

    /**
     * A value-less substructure (e.g. an empty `FORM` line) is shaped as a NULL leaf, which must
     * resolve as absent rather than failing the whole record — the place still maps, with a null
     * form.
     */
    #[Test]
    public function mapsAPlaceWithAnEmptyFormLineWithoutFailing(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston, Massachusetts\n3 FORM\n0 TRLR\n"
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
        self::assertNull($place->form, 'an empty FORM line resolves to a null form, not a mapping failure');
        self::assertSame(['Boston', 'Massachusetts'], $place->levels);
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
     * A GEDCOM 7.0 DATE and AGE declare PHRASE/TIME substructures, so their payload is shaped as an
     * array (with the line value under the `value` key) rather than a bare string. The date and age
     * handlers must resolve the leaf value from that shaped array; a regression here would throw on
     * every 7.0 event that carries a dated substructure.
     */
    #[Test]
    public function mapsAGedcom7EventWhoseDateAndAgeCarrySubstructures(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 BIRT\n"
            . "2 DATE 1 JAN 2000\n3 PHRASE New Year's Day\n"
            . "2 AGE 0y\n3 PHRASE at birth\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V70);
        $definition = $schema->byUri('https://gedcom.io/terms/v7/record-INDI');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, IndividualRecord::class);

        self::assertInstanceOf(IndividualRecord::class, $record);
        self::assertCount(1, $record->birt, 'a single BIRT maps to a one-element list');
        $birth = $record->birt[0];

        // The DATE arrives as a shaped array (it carries a PHRASE), and its value is still parsed.
        self::assertInstanceOf(DateValue::class, $birth->date);
        self::assertSame('1 JAN 2000', $birth->date->raw, 'the DATE value is resolved from the shaped array');

        // Likewise the AGE, shaped because it carries a PHRASE.
        self::assertInstanceOf(AgeValue::class, $birth->age);
        self::assertSame(0, $birth->age->years, 'the AGE value is resolved from the shaped array');
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
        self::assertCount(1, $record->birt, 'a single BIRT maps to a one-element list');
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
     * An individual maps its death and burial events (each {0:M}) into typed EventDetail lists
     * alongside birth. A DEAT carries a direct AGE substructure, so the death event's age leaf is
     * populated, while a BURI carries the place of burial.
     */
    #[Test]
    public function mapsAnIndividualDeathAndBurialEvents(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n"
            . "1 DEAT\n2 DATE 3 MAR 1980\n2 AGE 80y\n"
            . "1 BURI\n2 DATE 7 MAR 1980\n2 PLAC Boston, Massachusetts\n"
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

        self::assertCount(1, $record->deat, 'a single DEAT maps to a one-element list');
        $death = $record->deat[0];
        self::assertInstanceOf(EventDetail::class, $death);
        self::assertInstanceOf(DateValue::class, $death->date);
        self::assertSame('3 MAR 1980', $death->date->raw);
        self::assertInstanceOf(AgeValue::class, $death->age, 'a DEAT carries a direct AGE substructure');
        self::assertSame(80, $death->age->years);

        self::assertCount(1, $record->buri, 'a single BURI maps to a one-element list');
        $burial = $record->buri[0];
        self::assertInstanceOf(EventDetail::class, $burial);
        self::assertInstanceOf(DateValue::class, $burial->date);
        self::assertSame('7 MAR 1980', $burial->date->raw);
        self::assertInstanceOf(PlaceValue::class, $burial->plac);
        self::assertSame(['Boston', 'Massachusetts'], $burial->plac->levels);
    }

    /**
     * An individual maps its child-to-family (FAMC) and spouse-to-family (FAMS) links, each {0:M}.
     * Because both declare substructures, the shaped node is an object rather than a bare pointer:
     * FAMC becomes a typed ChildToFamilyLink carrying the family cross-reference and the optional
     * pedigree, while FAMS becomes a SpouseToFamilyLink carrying just the cross-reference.
     */
    #[Test]
    public function mapsAnIndividualChildAndSpouseFamilyLinks(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n"
            . "1 FAMC @F1@\n2 PEDI birth\n"
            . "1 FAMS @F2@\n"
            . "1 FAMS @F3@\n"
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

        self::assertCount(1, $record->famc, 'a single FAMC maps to a one-element list');
        $childLink = $record->famc[0];
        self::assertInstanceOf(ChildToFamilyLink::class, $childLink);
        self::assertSame('F1', $childLink->xref, 'the FAMC pointer maps to the family cross-reference');
        self::assertSame('birth', $childLink->pedi, 'the PEDI substructure is threaded through');

        self::assertCount(2, $record->fams, 'the repeatable FAMS links map to a list');
        self::assertContainsOnlyInstancesOf(SpouseToFamilyLink::class, $record->fams);
        self::assertSame(['F2', 'F3'], [$record->fams[0]->xref, $record->fams[1]->xref]);
    }

    /**
     * A malformed, pointer-less family link (a `FAMC`/`FAMS` line with no cross-reference) carries
     * no usable link, so it is tolerantly skipped rather than failing the whole record — a valid
     * sibling link on the same individual still maps.
     */
    #[Test]
    public function skipsAValuelessFamilyLinkAndKeepsTheValidOne(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 FAMC\n1 FAMS @F2@\n0 TRLR\n"
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
        self::assertSame([], $record->famc, 'a pointer-less FAMC is skipped rather than mapped or fatal');

        self::assertCount(1, $record->fams, 'the valid FAMS on the same record still maps');
        self::assertSame('F2', $record->fams[0]->xref);
    }

    /**
     * A family record maps its partner and child cross-reference pointers (HUSB/WIFE single, CHIL
     * repeatable) and its repeatable marriage events, each a typed EventDetail carrying the shared
     * event's date and place.
     */
    #[Test]
    public function mapsAFamilyRecordWithPartnersChildrenAndMarriage(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @F1@ FAM\n"
            . "1 HUSB @I1@\n"
            . "1 WIFE @I2@\n"
            . "1 CHIL @I3@\n"
            . "1 CHIL @I4@\n"
            . "1 MARR\n2 DATE 14 FEB 1990\n2 PLAC Boston, Massachusetts\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/record-FAM');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, FamilyRecord::class);

        self::assertInstanceOf(FamilyRecord::class, $record);
        self::assertSame('F1', $record->xref);
        self::assertSame('I1', $record->husb, 'the HUSB pointer maps to the individual cross-reference');
        self::assertSame('I2', $record->wife, 'the WIFE pointer maps to the individual cross-reference');
        self::assertSame(['I3', 'I4'], $record->chil, 'the repeatable CHIL pointers map to a list');

        self::assertCount(1, $record->marr, 'a single MARR maps to a one-element list');
        $marriage = $record->marr[0];
        self::assertInstanceOf(EventDetail::class, $marriage);
        self::assertInstanceOf(DateValue::class, $marriage->date);
        self::assertSame('14 FEB 1990', $marriage->date->raw);
        self::assertInstanceOf(PlaceValue::class, $marriage->plac);
        self::assertSame(['Boston', 'Massachusetts'], $marriage->plac->levels);
    }

    /**
     * A source record maps each of its single descriptive text leaves — title, author,
     * publication facts, abbreviation and verbatim text — onto the typed SourceRecord as an
     * optional string, so every field's tag-to-property wiring is exercised.
     */
    #[Test]
    public function mapsASourceRecordDescriptiveFields(): void
    {
        $record = $this->mapSource(
            "0 @S1@ SOUR\n"
            . "1 TITL Vital Records of Boston\n"
            . "1 AUTH City of Boston\n"
            . "1 PUBL Boston, 1901\n"
            . "1 ABBR Boston VR\n"
            . "1 TEXT Verbatim register extract\n"
            . "0 TRLR\n"
        );

        self::assertSame('S1', $record->xref);
        self::assertSame('Vital Records of Boston', $record->titl);
        self::assertSame('City of Boston', $record->auth);
        self::assertSame('Boston, 1901', $record->publ);
        self::assertSame('Boston VR', $record->abbr);
        self::assertSame('Verbatim register extract', $record->text);
    }

    /**
     * A source record carrying only its identifier maps every optional descriptive leaf to null
     * rather than to an empty string or a fatal.
     */
    #[Test]
    public function mapsASourceRecordWithNoDescriptiveFieldsAsAllNull(): void
    {
        $record = $this->mapSource("0 @S1@ SOUR\n0 TRLR\n");

        self::assertSame('S1', $record->xref);
        self::assertNull($record->titl);
        self::assertNull($record->auth);
        self::assertNull($record->publ);
        self::assertNull($record->abbr);
        self::assertNull($record->text);
    }

    /**
     * Maps a source record from an in-memory GEDCOM string onto the typed model.
     */
    private function mapSource(string $gedcom): SourceRecord
    {
        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri('https://gedcom.io/terms/v5.5.1/record-SOUR');
        self::assertInstanceOf(StructureDefinition::class, $definition);

        return (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, SourceRecord::class);
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
