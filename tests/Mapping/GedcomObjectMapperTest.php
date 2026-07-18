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
use MagicSunday\Gedcom\Model\ChangeDate;
use MagicSunday\Gedcom\Model\ChildToFamilyLink;
use MagicSunday\Gedcom\Model\CreationDate;
use MagicSunday\Gedcom\Model\EventDetail;
use MagicSunday\Gedcom\Model\ExactDate;
use MagicSunday\Gedcom\Model\ExternalIdentifier;
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\MediaFormat;
use MagicSunday\Gedcom\Model\Medium;
use MagicSunday\Gedcom\Model\MultimediaFile;
use MagicSunday\Gedcom\Model\MultimediaRecord;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\NoteRecord;
use MagicSunday\Gedcom\Model\NoteTranslation;
use MagicSunday\Gedcom\Model\PersonalName;
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\SpouseToFamilyLink;
use MagicSunday\Gedcom\Model\SubmitterRecord;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\Schema\StructureDefinition;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\AgeKeyword;
use MagicSunday\Gedcom\ValueObject\AgeModifier;
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\CalendarDate;
use MagicSunday\Gedcom\ValueObject\DateType;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\MapCoordinates;
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
#[CoversClass(ExternalIdentifier::class)]
#[CoversClass(CreationDate::class)]
#[CoversClass(ExactDate::class)]
#[CoversClass(ChangeDate::class)]
#[CoversClass(Note::class)]
#[CoversClass(NoteTranslation::class)]
#[CoversClass(EventDetail::class)]
#[CoversClass(PersonalName::class)]
#[CoversClass(FamilyRecord::class)]
#[CoversClass(ChildToFamilyLink::class)]
#[CoversClass(SpouseToFamilyLink::class)]
#[CoversClass(SourceRecord::class)]
#[CoversClass(NoteRecord::class)]
#[CoversClass(RepositoryRecord::class)]
#[CoversClass(MultimediaRecord::class)]
#[CoversClass(MultimediaFile::class)]
#[CoversClass(MediaFormat::class)]
#[CoversClass(Medium::class)]
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
#[UsesClass(MapCoordinates::class)]
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
    public function mapsASubmitterRecordOntoTheModel(): void
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

        // The DATE arrives as a shaped array (it carries a PHRASE), and its value is still parsed
        // while the PHRASE substructure is threaded onto the value object.
        self::assertInstanceOf(DateValue::class, $birth->date);
        self::assertSame('1 JAN 2000', $birth->date->raw, 'the DATE value is resolved from the shaped array');
        self::assertSame("New Year's Day", $birth->date->phrase, 'the DATE PHRASE substructure is threaded through');

        // Likewise the AGE, shaped because it carries a PHRASE.
        self::assertInstanceOf(AgeValue::class, $birth->age);
        self::assertSame(0, $birth->age->years, 'the AGE value is resolved from the shaped array');
        self::assertSame('at birth', $birth->age->phrase, 'the AGE PHRASE substructure is threaded through');
    }

    /**
     * A GEDCOM 7.0 value-less DATE and AGE carried solely by a PHRASE substructure — a free-text
     * form for a value that does not fit the standard grammar — thread the phrase onto the typed
     * value objects rather than dropping it: the DATE becomes a phrase-typed date and the AGE
     * records only its phrase.
     */
    #[Test]
    public function mapsAGedcom7EventWhoseDateAndAgeAreCarriedOnlyByAPhrase(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 BIRT\n"
            . "2 DATE\n3 PHRASE around harvest time\n"
            . "2 AGE\n3 PHRASE a young child\n"
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
        $birth = $record->birt[0];

        self::assertInstanceOf(DateValue::class, $birth->date);
        self::assertSame(DateType::Phrase, $birth->date->type, 'a value-less DATE with a PHRASE is a phrase date');
        self::assertNull($birth->date->date, 'a phrase-only date has no calendar date');
        self::assertSame('around harvest time', $birth->date->phrase);

        self::assertInstanceOf(AgeValue::class, $birth->age);
        self::assertNull($birth->age->years, 'a phrase-only age has no duration');
        self::assertNull($birth->age->keyword, 'a phrase-only age has no keyword');
        self::assertSame('a young child', $birth->age->phrase);
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
        self::assertNull($birth->plac->coordinates, 'a PLAC without a MAP has no coordinates');

        self::assertInstanceOf(AgeValue::class, $birth->age);
        self::assertSame(0, $birth->age->years);
    }

    /**
     * A PLAC carrying a MAP substructure threads the LATI/LONG coordinates onto the typed
     * PlaceValue as signed decimal degrees (north/east positive, south/west negative).
     */
    #[Test]
    public function mapsThePlaceMapCoordinates(): void
    {
        $record = $this->mapIndividual(
            "0 @I1@ INDI\n1 BIRT\n"
            . "2 PLAC Boston, Massachusetts, USA\n"
            . "3 MAP\n4 LATI N42.3601\n4 LONG W71.0589\n"
            . "0 TRLR\n"
        );

        self::assertCount(1, $record->birt, 'a single BIRT maps to a one-element list');
        $place = $record->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);

        self::assertInstanceOf(MapCoordinates::class, $place->coordinates, 'the MAP substructure is threaded through');
        self::assertSame(42.3601, $place->coordinates->latitude);
        self::assertSame(-71.0589, $place->coordinates->longitude);
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
        self::assertSame('John /Doe/', $primary->value, 'the NAME value string is preserved');
        self::assertSame('John', $primary->givn, 'the GIVN name-part substructure is mapped');
        self::assertSame('Doe', $primary->surn, 'the SURN name-part substructure is mapped');
        $secondary = $record->name[1];
        self::assertSame('Johnny /Doe/', $secondary->value);
        self::assertNull($secondary->givn, 'a NAME with no GIVN part maps to null');
        self::assertNull($secondary->surn, 'a NAME with no SURN part maps to null');

        self::assertCount(1, $record->birt, 'the single BIRT maps to a one-element list');
        self::assertInstanceOf(DateValue::class, $record->birt[0]->date);
    }

    /**
     * Every {0:1} name-part substructure (GIVN, SURN, NPFX, SPFX, NSFX, NICK, TYPE) is shaped by
     * the schema onto the typed name, and the resulting object interprets the slash convention
     * end-to-end so that its display name is slash-free. The slash-derivation branches themselves
     * are exercised directly in PersonalNameTest.
     */
    #[Test]
    public function mapsEveryNamePartSubstructureOntoTheTypedName(): void
    {
        $record = $this->mapIndividual(
            "0 @I1@ INDI\n"
            . "1 NAME Johnny /Doe/ Jr\n"
            . "2 TYPE birth\n"
            . "2 NPFX Dr\n"
            . "2 GIVN Jonathan\n"
            . "2 SPFX van\n"
            . "2 SURN Smith\n"
            . "2 NSFX PhD\n"
            . "2 NICK Johnny\n"
            . "0 TRLR\n"
        );

        self::assertCount(1, $record->name);
        $name = $record->name[0];

        self::assertSame('Johnny /Doe/ Jr', $name->value, 'the raw value is preserved');
        self::assertSame('Jonathan', $name->givn);
        self::assertSame('Smith', $name->surn);
        self::assertSame('Dr', $name->npfx);
        self::assertSame('van', $name->spfx);
        self::assertSame('PhD', $name->nsfx);
        self::assertSame('Johnny', $name->nick);
        self::assertSame('birth', $name->type);

        // One end-to-end assertion that the mapped object is a usable, slash-free name; the
        // derivation rules themselves live in PersonalNameTest.
        self::assertSame('Johnny Doe Jr', $name->getDisplayName(), 'the display name strips the slashes');
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
     * A failure inside the underlying mapper — here a record with no cross-reference identifier, so
     * the required string xref is null at construction and surfaces as a TypeError rather than a
     * mapper exception — is wrapped in the library's own MappingException, so every mapping failure
     * stays within the shared exception group.
     */
    #[Test]
    public function wrapsAMapperFailureInAMappingException(): void
    {
        $this->expectException(MappingException::class);

        // The record carries no @xref@, so the required string xref is null at construction.
        $this->mapSubmitter("0 SUBM\n1 NAME John Doe\n0 TRLR\n");
    }

    /**
     * A submitter record with no NAME line maps to a submitter whose name is null rather than
     * failing the whole document — real files carry bare `0 @SUBM@ SUBM` records, and the spec's
     * {1:1} NAME requirement is treated tolerantly.
     */
    #[Test]
    public function mapsASubmitterWithoutANameToANullName(): void
    {
        $record = $this->mapSubmitter("0 @SUBM1@ SUBM\n0 TRLR\n");

        self::assertSame('SUBM1', $record->xref);
        self::assertNull($record->name, 'a name-less SUBM maps to a null name, not a failure');
        self::assertSame([], $record->phon);
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
        self::assertInstanceOf(DateValue::class, $death->date);
        self::assertSame('3 MAR 1980', $death->date->raw);
        self::assertInstanceOf(AgeValue::class, $death->age, 'a DEAT carries a direct AGE substructure');
        self::assertSame(80, $death->age->years);

        self::assertCount(1, $record->buri, 'a single BURI maps to a one-element list');
        $burial = $record->buri[0];
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
        self::assertSame('F1', $childLink->xref, 'the FAMC pointer maps to the family cross-reference');
        self::assertSame('birth', $childLink->pedi, 'the PEDI substructure is threaded through');

        self::assertCount(2, $record->fams, 'the repeatable FAMS links map to a list');
        self::assertContainsOnlyInstancesOf(SpouseToFamilyLink::class, $record->fams);
        self::assertSame(['F2', 'F3'], [$record->fams[0]->xref, $record->fams[1]->xref]);
    }

    /**
     * A malformed, pointer-less family link (a `FAMC`/`FAMS` line with no cross-reference) carries no
     * usable link, but it is mapped as an empty one rather than failing the record or vanishing from
     * it — a valid sibling link on the same individual maps as it always did.
     */
    #[Test]
    public function mapsAValuelessFamilyLinkAsAnEmptyLinkAndKeepsTheValidOne(): void
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
        // A pointer-less FAMC carries nothing, but it is mapped rather than dropped, consistently
        // with every other container the model tolerates empty (a spouse age without its age, an
        // empty place line). It used to be skipped only because the missing pointer left the model
        // unconstructible; now that the pointer is tolerant, the line no longer disappears.
        self::assertCount(1, $record->famc, 'a pointer-less FAMC maps as an empty link rather than vanishing');
        self::assertNull($record->famc[0]->xref);
        self::assertNull($record->famc[0]->value);

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
     * A descriptive leaf split across CONC/CONT continuation lines maps to its full reassembled
     * value, not just the first physical line — the typed model reflects the whole logical text.
     */
    #[Test]
    public function mapsASourceTitleReassembledFromContinuationLines(): void
    {
        $record = $this->mapSource(
            "0 @S1@ SOUR\n"
            . "1 TITL A very long\n2 CONC  source title\n2 CONT with a second line\n"
            . "0 TRLR\n"
        );

        self::assertSame(
            "A very long source title\nwith a second line",
            $record->titl,
            'the CONC/CONT continuation lines are reassembled into the typed title'
        );
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
     * Maps the single SOUR record in the GEDCOM source onto the typed SourceRecord.
     *
     * @param string $gedcom The GEDCOM source carrying one SOUR record.
     *
     * @return SourceRecord The hydrated source record.
     */
    private function mapSource(string $gedcom): SourceRecord
    {
        return $this->mapRecordViaSchema($gedcom, 'https://gedcom.io/terms/v5.5.1/record-SOUR', SourceRecord::class);
    }

    /**
     * Maps the single INDI record in the GEDCOM source onto the typed IndividualRecord.
     *
     * @param string $gedcom The GEDCOM source carrying one INDI record.
     *
     * @return IndividualRecord The hydrated individual record.
     */
    private function mapIndividual(string $gedcom): IndividualRecord
    {
        return $this->mapRecordViaSchema($gedcom, 'https://gedcom.io/terms/v5.5.1/record-INDI', IndividualRecord::class);
    }

    /**
     * Reads the single level-0 record from the GEDCOM source and maps it, through the 5.5.1
     * registry schema, onto the given typed record class.
     *
     * @template TRecord of object
     *
     * @param string                $gedcom    The GEDCOM source carrying one level-0 record.
     * @param string                $recordUri The registry URI of that record's structure.
     * @param class-string<TRecord> $class     The typed record class to hydrate.
     *
     * @return TRecord The hydrated typed record.
     */
    private function mapRecordViaSchema(string $gedcom, string $recordUri, string $class): object
    {
        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition = $schema->byUri($recordUri);
        self::assertInstanceOf(StructureDefinition::class, $definition);

        return (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->map($node, $definition, $class);
    }

    /**
     * A multimedia record maps its repeatable FILE references onto typed MultimediaFile objects,
     * each nesting a typed MediaFormat (format plus optional media type) and an optional title.
     * The two files exercise the fully-populated path and the minimal path (no title, no media
     * type) so the optional leaves are covered present and absent.
     */
    #[Test]
    public function mapsAMultimediaRecordWithNestedFileFormats(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @O1@ OBJE\n"
            . "1 FILE http://example.test/portrait.jpg\n2 FORM jpg\n3 TYPE photo\n2 TITL Family portrait\n"
            . "1 FILE http://example.test/register.tif\n2 FORM tif\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, MultimediaRecord::class);

        self::assertInstanceOf(MultimediaRecord::class, $record);
        self::assertSame('O1', $record->xref);
        self::assertCount(2, $record->file, 'the repeatable FILE references map to a list');

        $portrait = $record->file[0];
        self::assertSame('http://example.test/portrait.jpg', $portrait->value);
        self::assertSame('Family portrait', $portrait->titl);
        self::assertInstanceOf(MediaFormat::class, $portrait->form);
        self::assertSame('jpg', $portrait->form->value);
        self::assertSame('photo', $portrait->form->type, 'the nested FORM TYPE is threaded through');
        self::assertNull($portrait->form->medi, 'a 5.5.1 FORM has no 7.0 MEDI');

        $register = $record->file[1];
        self::assertSame('http://example.test/register.tif', $register->value);
        self::assertNull($register->titl, 'an absent FILE title stays null');
        self::assertInstanceOf(MediaFormat::class, $register->form);
        self::assertSame('tif', $register->form->value);
        self::assertNull($register->form->type, 'an absent FORM TYPE stays null');
        self::assertNull($register->form->medi, 'a 5.5.1 FORM has no 7.0 MEDI');
    }

    /**
     * A GEDCOM 7.0 multimedia file classifies its medium with the enumerated `MEDI` (not 5.5.1's
     * free-text `TYPE`), which itself may carry a `PHRASE` for an `OTHER` medium. It maps onto a
     * typed {@see Medium} nested in the file's {@see MediaFormat}. Because a 7.0 `MEDI` shapes to a
     * substructure-bearing array, this also guards the regression where such a FORM mapped to NULL.
     */
    #[Test]
    public function mapsThe70MediumClassificationOfAMultimediaFile(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @O1@ OBJE\n"
            . "1 FILE http://example.test/portrait.jpg\n2 FORM image/jpeg\n3 MEDI PHOTO\n"
            . "1 FILE http://example.test/scan.tiff\n2 FORM image/tiff\n3 MEDI OTHER\n4 PHRASE Digital scan of a newspaper\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V70);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, MultimediaRecord::class);

        self::assertInstanceOf(MultimediaRecord::class, $record);
        self::assertCount(2, $record->file);

        $portrait = $record->file[0];
        self::assertInstanceOf(MediaFormat::class, $portrait->form, 'a 7.0 FORM carrying MEDI must not map to null');
        self::assertSame('image/jpeg', $portrait->form->value);
        self::assertNull($portrait->form->type, 'a 7.0 FORM has no 5.5.1 TYPE');
        self::assertInstanceOf(Medium::class, $portrait->form->medi);
        self::assertSame('PHOTO', $portrait->form->medi->value);
        self::assertNull($portrait->form->medi->phrase, 'a plain enumerated medium carries no phrase');

        $scan = $record->file[1];
        self::assertInstanceOf(MediaFormat::class, $scan->form);
        self::assertInstanceOf(Medium::class, $scan->form->medi);
        self::assertSame('OTHER', $scan->form->medi->value);
        self::assertSame('Digital scan of a newspaper', $scan->form->medi->phrase, 'the OTHER medium keeps its phrase');
    }

    /**
     * GEDCOM 7.0 lets a record carry external identifiers: any number of `UID` values (mapped to a
     * list of raw strings) and any number of `EXID` identifiers, each with an optional `TYPE`
     * authority URI (mapped to a typed {@see ExternalIdentifier}). A `TYPE`-less `EXID` must still
     * map — the deprecated form keeps its value with a null type rather than being dropped.
     */
    #[Test]
    public function mapsThe70RecordLevelExternalIdentifiers(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n"
            . "1 UID 26D3EFD0-7A3C-4E1B-8F7A-1A2B3C4D5E6F\n"
            . "1 UID 7F1E2D3C-4B5A-6978-8A9B-0C1D2E3F4A5B\n"
            . "1 EXID 12345\n2 TYPE http://authority.example/tree\n"
            . "1 EXID 67890\n"
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
        self::assertSame(
            [
                '26D3EFD0-7A3C-4E1B-8F7A-1A2B3C4D5E6F',
                '7F1E2D3C-4B5A-6978-8A9B-0C1D2E3F4A5B',
            ],
            $record->uid,
            'the repeatable UID values map to a list of raw strings'
        );

        self::assertCount(2, $record->exid, 'the repeatable EXID identifiers map to a list');
        $issued = $record->exid[0];
        self::assertSame('12345', $issued->value);
        self::assertSame('http://authority.example/tree', $issued->type, 'the EXID TYPE authority URI is threaded through');
        $bare = $record->exid[1];
        self::assertSame('67890', $bare->value, 'a TYPE-less EXID keeps its value rather than being dropped');
        self::assertNull($bare->type, 'a TYPE-less EXID maps to a null type');
    }

    /**
     * The record-level `UID`/`EXID` identifiers are a GEDCOM 7.0 addition: the 5.5.1 grammar does not
     * permit them on a record, so they must stay empty when the same lines are read against the
     * 5.5.1 schema rather than leaking into the mapped record.
     */
    #[Test]
    public function doesNotMapRecordLevelExternalIdentifiersForGedcom551(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n"
            . "1 NAME John /Doe/\n"
            . "1 UID 26D3EFD0-7A3C-4E1B-8F7A-1A2B3C4D5E6F\n"
            . "1 EXID 12345\n2 TYPE http://authority.example/tree\n"
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
        self::assertSame([], $record->uid, 'a 5.5.1 record carries no UID');
        self::assertSame([], $record->exid, 'a 5.5.1 record carries no EXID');
    }

    /**
     * GEDCOM 7.0 records the moment a record was created in a `CREA` substructure whose `DATE` uses
     * the restricted exact-date grammar with an optional `TIME`. It maps onto a typed
     * {@see CreationDate} nesting an {@see ExactDate} — the raw date and time strings, deliberately
     * not parsed into a genealogical `DateValue`.
     */
    #[Test]
    public function mapsThe70RecordCreationTimestamp(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n"
            . "1 CREA\n2 DATE 1 JAN 2000\n3 TIME 12:00:00\n"
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
        self::assertInstanceOf(CreationDate::class, $record->crea, 'a CREA carrying a DATE must not map to null');
        self::assertInstanceOf(ExactDate::class, $record->crea->date);
        self::assertSame('1 JAN 2000', $record->crea->date->value, 'the exact date is kept as its raw string');
        self::assertSame('12:00:00', $record->crea->date->time, 'the accompanying TIME is threaded through');
    }

    /**
     * The creation timestamp is optional and 7.0-only: a 7.0 record without a `CREA` maps it to
     * null, and a 5.5.1 record cannot carry `CREA` at all, so it stays null there as well.
     */
    #[Test]
    public function leavesTheCreationTimestampNullWhenAbsentOrForGedcom551(): void
    {
        $withoutCrea = (new StreamFactory())->createStream("0 @I1@ INDI\n1 SEX M\n0 TRLR\n");
        $withoutCrea->rewind();

        $node = (new GedcomTreeReader(new Reader($withoutCrea)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema70 = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V70);
        $definition70 = $schema70->byUri('https://gedcom.io/terms/v7/record-INDI');
        self::assertInstanceOf(StructureDefinition::class, $definition70);

        $mapper   = new GedcomObjectMapper($schema70, JsonMapperFactory::create());
        $record70 = $mapper->map($node, $definition70, IndividualRecord::class);
        self::assertInstanceOf(IndividualRecord::class, $record70);
        self::assertNull($record70->crea, 'a 7.0 record without CREA maps the creation timestamp to null');

        $with551 = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 CREA\n2 DATE 1 JAN 2000\n3 TIME 12:00:00\n0 TRLR\n"
        );
        $with551->rewind();

        $node551 = (new GedcomTreeReader(new Reader($with551)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node551);

        $schema551 = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);
        $definition551 = $schema551->byUri('https://gedcom.io/terms/v5.5.1/record-INDI');
        self::assertInstanceOf(StructureDefinition::class, $definition551);

        $record551 = (new GedcomObjectMapper($schema551, JsonMapperFactory::create()))
            ->map($node551, $definition551, IndividualRecord::class);
        self::assertInstanceOf(IndividualRecord::class, $record551);
        self::assertNull($record551->crea, 'a 5.5.1 record carries no CREA');
    }

    /**
     * A GEDCOM 7.0 record documents its last change in a `CHAN` substructure: an exact date with an
     * optional time, plus any inline `NOTE`s (with their 7.0 language, media type and translations)
     * and `SNOTE` references to shared notes. It maps onto a typed {@see ChangeDate}. An inline note
     * carrying a source citation still maps — the unmodelled citation is dropped rather than mapping
     * the whole note away.
     */
    #[Test]
    public function mapsThe70RecordChangeTimestampWithNotes(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n"
            . "1 CHAN\n2 DATE 2 JAN 2001\n3 TIME 13:00:00\n"
            . "2 NOTE A recorded change\n3 LANG en\n3 MIME text/plain\n3 TRAN Een geregistreerde wijziging\n4 LANG nl\n"
            . "2 NOTE Change with a citation\n3 SOUR @S1@\n4 PAGE p. 42\n"
            . "2 SNOTE @N1@\n"
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
        self::assertInstanceOf(ChangeDate::class, $record->chan, 'a CHAN carrying notes must not map to null');
        self::assertInstanceOf(ExactDate::class, $record->chan->date);
        self::assertSame('2 JAN 2001', $record->chan->date->value);
        self::assertSame('13:00:00', $record->chan->date->time);

        self::assertCount(2, $record->chan->note, 'both inline NOTE lines map to a list');
        $documented = $record->chan->note[0];
        self::assertSame('A recorded change', $documented->value);
        self::assertSame('en', $documented->lang, 'the note LANG is threaded through');
        self::assertSame('text/plain', $documented->mime, 'the note MIME is threaded through');
        self::assertCount(1, $documented->tran, 'the note translation maps to a list');
        self::assertSame('Een geregistreerde wijziging', $documented->tran[0]->value);
        self::assertSame('nl', $documented->tran[0]->lang);

        $cited = $record->chan->note[1];
        self::assertSame('Change with a citation', $cited->value, 'a note with an unmodelled SOUR citation still maps its text');

        self::assertSame(['N1'], $record->chan->snote, 'the SNOTE reference maps to the shared-note cross-reference');
    }

    /**
     * The change timestamp exists in both GEDCOM versions, so a 5.5.1 record's `CHAN` maps too: its
     * `DATE` uses the same exact-date grammar, and its notes — inline submitter text and pointers to
     * shared notes alike — are carried as the plain note value (5.5.1 has no separate `SNOTE`).
     */
    #[Test]
    public function mapsThe551RecordChangeTimestampNotesAsPlainText(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n"
            . "1 CHAN\n2 DATE 2 JAN 2001\n3 TIME 13:00:00\n"
            . "2 NOTE A submitter change note\n"
            . "2 NOTE @N1@\n"
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
        self::assertInstanceOf(ChangeDate::class, $record->chan);
        self::assertInstanceOf(ExactDate::class, $record->chan->date, 'the 5.5.1 CHAN.DATE reuses the exact-date shape');
        self::assertSame('2 JAN 2001', $record->chan->date->value);
        self::assertSame('13:00:00', $record->chan->date->time);

        self::assertCount(2, $record->chan->note, 'both 5.5.1 CHAN notes map');
        self::assertSame('A submitter change note', $record->chan->note[0]->value, 'the inline submitter text survives');
        self::assertSame('N1', $record->chan->note[1]->value, 'a pointer note carries the shared-note cross-reference as its value');
        self::assertSame([], $record->chan->snote, 'a 5.5.1 record has no separate SNOTE reference');
    }

    /**
     * A record without a `CHAN` maps the change timestamp to null in both GEDCOM versions.
     */
    #[Test]
    public function leavesTheChangeTimestampNullWhenAbsent(): void
    {
        $mapNode = static function (string $gedcom, GedcomVersion $version, string $uri): ?ChangeDate {
            $stream = (new StreamFactory())->createStream($gedcom);
            $stream->rewind();

            $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
            self::assertInstanceOf(GedcomNode::class, $node);

            $schema     = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))->load($version);
            $definition = $schema->byUri($uri);
            self::assertInstanceOf(StructureDefinition::class, $definition);

            $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
                ->map($node, $definition, IndividualRecord::class);
            self::assertInstanceOf(IndividualRecord::class, $record);

            return $record->chan;
        };

        self::assertNull(
            $mapNode("0 @I1@ INDI\n1 SEX M\n0 TRLR\n", GedcomVersion::V70, 'https://gedcom.io/terms/v7/record-INDI'),
            'a 7.0 record without CHAN maps the change timestamp to null'
        );
        self::assertNull(
            $mapNode("0 @I1@ INDI\n1 SEX M\n0 TRLR\n", GedcomVersion::V551, 'https://gedcom.io/terms/v5.5.1/record-INDI'),
            'a 5.5.1 record without CHAN maps the change timestamp to null'
        );
    }

    /**
     * The inline-note handler builds a note defensively from a mis-shaped payload: a note leaf that
     * is neither a string nor a shaped array yields an empty note rather than failing, and a
     * translation element that is not a shaped array is skipped rather than dropping the whole note.
     */
    #[Test]
    public function buildsInlineChangeNotesDefensivelyFromMalformedShapes(): void
    {
        $mapper = JsonMapperFactory::create();

        $misShaped = $mapper->map(['note' => [42]], ChangeDate::class);
        self::assertInstanceOf(ChangeDate::class, $misShaped);
        self::assertCount(1, $misShaped->note);
        self::assertNull($misShaped->note[0]->value, 'a mis-shaped note payload maps to an empty note');

        $withTran = $mapper->map(
            ['note' => [['value' => 'text', 'tran' => ['skip-me', ['value' => 'vertaald', 'lang' => 'nl']]]]],
            ChangeDate::class,
        );
        self::assertInstanceOf(ChangeDate::class, $withTran);
        self::assertCount(1, $withTran->note[0]->tran, 'a non-array translation element is skipped');
        self::assertSame('vertaald', $withTran->note[0]->tran[0]->value);
        self::assertSame('nl', $withTran->note[0]->tran[0]->lang);

        $nonListTran = $mapper->map(['note' => [['value' => 'text', 'tran' => 'not-a-list']]], ChangeDate::class);
        self::assertInstanceOf(ChangeDate::class, $nonListTran);
        self::assertSame([], $nonListTran->note[0]->tran, 'a non-array tran payload yields no translations');
    }

    /**
     * A malformed, reference-less FILE must not drop the whole file list: the record still maps
     * and a valid sibling FILE survives, while the malformed one maps to a null reference.
     */
    #[Test]
    public function keepsAValidMultimediaFileAlongsideAReferencelessOne(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @O1@ OBJE\n1 FILE http://example.test/portrait.jpg\n2 FORM jpg\n1 FILE\n2 FORM tif\n0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, MultimediaRecord::class);

        self::assertInstanceOf(MultimediaRecord::class, $record);
        self::assertCount(2, $record->file, 'a reference-less FILE does not drop the valid sibling');
        self::assertSame('http://example.test/portrait.jpg', $record->file[0]->value, 'the valid file survives');
        self::assertNull($record->file[1]->value, 'the reference-less file maps to a null reference');
    }

    /**
     * A repository record maps its required name and its repeatable contact leaves (phone, email,
     * fax, each {0:3}) onto the typed RepositoryRecord, the contact fields as lists.
     */
    #[Test]
    public function mapsARepositoryRecordNameAndContacts(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @R1@ REPO\n"
            . "1 NAME City Archive\n"
            . "1 PHON 555-1000\n"
            . "1 PHON 555-1001\n"
            . "1 EMAIL archive@example.test\n"
            . "1 FAX 555-1099\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, RepositoryRecord::class);

        self::assertInstanceOf(RepositoryRecord::class, $record);
        self::assertSame('R1', $record->xref);
        self::assertSame('City Archive', $record->name);
        self::assertSame(['555-1000', '555-1001'], $record->phon, 'the repeatable PHON leaves map to a list');
        self::assertSame(['archive@example.test'], $record->email);
        self::assertSame(['555-1099'], $record->fax);
    }

    /**
     * A repository carrying only its required name maps the absent optional contact leaves to
     * empty lists rather than null, so a consumer can iterate them unconditionally.
     */
    #[Test]
    public function mapsARepositoryRecordWithoutContactsToEmptyLists(): void
    {
        $stream = (new StreamFactory())->createStream("0 @R2@ REPO\n1 NAME City Archive\n0 TRLR\n");
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, RepositoryRecord::class);

        self::assertInstanceOf(RepositoryRecord::class, $record);
        self::assertSame('City Archive', $record->name);
        self::assertSame([], $record->phon, 'an absent PHON maps to an empty list, not null');
        self::assertSame([], $record->email);
        self::assertSame([], $record->fax);
    }

    /**
     * A repository record with no NAME line maps to a repository whose name is null rather than
     * failing the whole document, tolerating the same bare-record shape as a name-less submitter.
     */
    #[Test]
    public function mapsARepositoryWithoutANameToANullName(): void
    {
        $stream = (new StreamFactory())->createStream("0 @R3@ REPO\n0 TRLR\n");
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, RepositoryRecord::class);

        self::assertInstanceOf(RepositoryRecord::class, $record);
        self::assertSame('R3', $record->xref);
        self::assertNull($record->name, 'a name-less REPO maps to a null name, not a failure');
    }

    /**
     * A shared note record maps its text — carried as the record's own line value and reassembled
     * across CONC/CONT continuation lines — onto the typed NoteRecord.
     */
    #[Test]
    public function mapsANoteRecordTextFromTheRecordValue(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @N1@ NOTE This is a shared note\n1 CONT spanning two lines\n0 TRLR\n"
        );
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, NoteRecord::class);

        self::assertInstanceOf(NoteRecord::class, $record);
        self::assertSame('N1', $record->xref);
        self::assertSame("This is a shared note\nspanning two lines", $record->value);
    }

    /**
     * A note record with no text maps to a null value rather than an empty string, honouring the
     * shaper's absent-value branch.
     */
    #[Test]
    public function mapsANoteRecordWithoutTextToANullValue(): void
    {
        $stream = (new StreamFactory())->createStream("0 @N1@ NOTE\n0 TRLR\n");
        $stream->rewind();

        $node = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $node);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $record = (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($node, NoteRecord::class);

        self::assertInstanceOf(NoteRecord::class, $record);
        self::assertSame('N1', $record->xref);
        self::assertNull($record->value, 'an empty note text maps to null, not an empty string');
    }

    /**
     * Maps the single SUBM record in the GEDCOM source onto the typed SubmitterRecord.
     *
     * @param string $gedcom The GEDCOM source carrying one SUBM record.
     *
     * @return SubmitterRecord The hydrated submitter record.
     */
    private function mapSubmitter(string $gedcom): SubmitterRecord
    {
        return $this->mapRecordViaSchema($gedcom, 'https://gedcom.io/terms/v5.5.1/record-SUBM', SubmitterRecord::class);
    }
}
