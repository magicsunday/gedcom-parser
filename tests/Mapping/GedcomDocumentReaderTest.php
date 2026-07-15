<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Mapping;

use MagicSunday\Gedcom\Mapping\GedcomDocumentReader;
use MagicSunday\Gedcom\Mapping\GedcomObjectMapper;
use MagicSunday\Gedcom\Mapping\GedcomVersionDetector;
use MagicSunday\Gedcom\Mapping\JsonMapperFactory;
use MagicSunday\Gedcom\Model\EventDetail;
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\MultimediaRecord;
use MagicSunday\Gedcom\Model\NoteRecord;
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\SubmitterRecord;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\DateType;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function basename;
use function dirname;
use function glob;

/**
 * Tests that the reader detects the schema version from the header and maps the whole stream into a
 * typed aggregate — reading a 5.5.1 and a 7.0 document (the latter proving the detected version
 * drives the schema, since a 7.0-only PHRASE is threaded), defaulting to 5.5.1 for a version-less
 * header, and returning an empty aggregate for an empty stream.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomDocumentReader::class)]
#[UsesClass(GedcomVersionDetector::class)]
#[UsesClass(GedcomObjectMapper::class)]
#[UsesClass(JsonMapperFactory::class)]
#[UsesClass(GedcomTreeReader::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(Reader::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(RegistrySchemaLoader::class)]
#[UsesClass(Schema::class)]
#[UsesClass(GedcomVersion::class)]
#[UsesClass(GedcomDocument::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(FamilyRecord::class)]
#[UsesClass(SourceRecord::class)]
#[UsesClass(NoteRecord::class)]
#[UsesClass(RepositoryRecord::class)]
#[UsesClass(MultimediaRecord::class)]
#[UsesClass(SubmitterRecord::class)]
#[UsesClass(EventDetail::class)]
#[UsesClass(DateValue::class)]
#[UsesClass(PlaceValue::class)]
class GedcomDocumentReaderTest extends TestCase
{
    /**
     * The standard level-0 record tags mapped onto their typed record classes.
     *
     * @var array<string, class-string>
     */
    private const array STANDARD_RECORD_CLASSES = [
        'INDI' => IndividualRecord::class,
        'FAM'  => FamilyRecord::class,
        'SOUR' => SourceRecord::class,
        'NOTE' => NoteRecord::class,
        'REPO' => RepositoryRecord::class,
        'OBJE' => MultimediaRecord::class,
        'SUBM' => SubmitterRecord::class,
    ];

    /**
     * A 5.5.1 document is read into the aggregate with its records grouped by type.
     */
    #[Test]
    public function readsA551DocumentIntoTheAggregate(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n"
            . "0 @I1@ INDI\n1 SEX M\n"
            . "0 @F1@ FAM\n1 HUSB @I1@\n"
            . "0 TRLR\n"
        );

        self::assertCount(1, $document->individuals);
        self::assertSame('I1', $document->individuals[0]->xref);
        self::assertCount(1, $document->families);
        self::assertSame('F1', $document->families[0]->xref);
    }

    /**
     * A 7.0 header makes the reader compile the 7.0 schema, so a value-less DATE carried only by a
     * PHRASE substructure — a 7.0-only construct — is threaded onto the typed date rather than
     * dropped. This proves the detected version, not the 5.5.1 default, drives the mapping.
     */
    #[Test]
    public function readsA70DocumentUsingTheDetectedSevenSchema(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 7.0\n"
            . "0 @I1@ INDI\n1 BIRT\n2 DATE\n3 PHRASE around harvest time\n"
            . "0 TRLR\n"
        );

        self::assertCount(1, $document->individuals);
        $birth = $document->individuals[0]->birt[0];
        self::assertInstanceOf(DateValue::class, $birth->date);
        self::assertSame(DateType::Phrase, $birth->date->type);
        self::assertSame('around harvest time', $birth->date->phrase);
    }

    /**
     * A header without a GEDC.VERS line falls back to the 5.5.1 baseline and still reads the
     * document's records.
     */
    #[Test]
    public function defaultsToTheBaselineForAVersionLessHeader(): void
    {
        $document = $this->read("0 HEAD\n0 @I1@ INDI\n1 SEX M\n0 TRLR\n");

        self::assertCount(1, $document->individuals);
        self::assertSame('I1', $document->individuals[0]->xref);
    }

    /**
     * An empty stream yields an empty aggregate rather than failing.
     */
    #[Test]
    public function readsAnEmptyStreamAsAnEmptyDocument(): void
    {
        $document = $this->read('');

        self::assertSame([], $document->individuals);
        self::assertSame([], $document->families);
    }

    /**
     * A place hierarchy declared once in the header (HEAD.PLAC.FORM) is threaded onto a place that
     * carries no FORM of its own, so its jurisdiction labels resolve — the common 5.5.1 case.
     */
    #[Test]
    public function threadsTheHeaderPlaceFormOntoAFormlessPlace(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 PLAC\n2 FORM City, County, State, Country\n"
            . "0 @I1@ INDI\n1 BIRT\n2 PLAC Cove, Cache, Utah, USA\n"
            . "0 TRLR\n"
        );

        $place = $document->individuals[0]->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(
            ['City' => 'Cove', 'County' => 'Cache', 'State' => 'Utah', 'Country' => 'USA'],
            $place->mapped(),
        );
    }

    /**
     * A place carrying its own FORM keeps it; the header default does not override a local FORM.
     */
    #[Test]
    public function aPlaceLocalFormOverridesTheHeaderDefault(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 PLAC\n2 FORM City, County, State, Country\n"
            . "0 @I1@ INDI\n1 BIRT\n2 PLAC Berlin\n3 FORM City\n"
            . "0 TRLR\n"
        );

        $place = $document->individuals[0]->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(['City' => 'Berlin'], $place->mapped());
    }

    /**
     * An explicitly empty local FORM counts as absent (as PlaceValue treats an empty FORM), so the
     * place still inherits the header default rather than the empty FORM suppressing it.
     */
    #[Test]
    public function anEmptyLocalFormStillInheritsTheHeaderDefault(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 PLAC\n2 FORM City, County, State, Country\n"
            . "0 @I1@ INDI\n1 BIRT\n2 PLAC Cove, Cache, Utah, USA\n3 FORM\n"
            . "0 TRLR\n"
        );

        $place = $document->individuals[0]->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(
            ['City' => 'Cove', 'County' => 'Cache', 'State' => 'Utah', 'Country' => 'USA'],
            $place->mapped(),
        );
    }

    /**
     * A whitespace-only local FORM is treated as absent as well, so the place still inherits the
     * header default rather than the blank FORM suppressing it.
     */
    #[Test]
    public function aWhitespaceOnlyLocalFormStillInheritsTheHeaderDefault(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 PLAC\n2 FORM City, County\n"
            . "0 @I1@ INDI\n1 BIRT\n2 PLAC Cove, Cache\n3 FORM   \n"
            . "0 TRLR\n"
        );

        $place = $document->individuals[0]->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(['City' => 'Cove', 'County' => 'Cache'], $place->mapped());
    }

    /**
     * Without a header FORM and without a local one, a form-less place still parses but produces no
     * jurisdiction map — the FORM threading must not invent one.
     */
    #[Test]
    public function aFormlessPlaceWithoutAHeaderFormHasNoMap(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n"
            . "0 @I1@ INDI\n1 BIRT\n2 PLAC Cove, Cache\n"
            . "0 TRLR\n"
        );

        $place = $document->individuals[0]->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertNull($place->form);
        self::assertSame([], $place->mapped());
    }

    /**
     * A present but empty PLAC line yields a non-null, empty PlaceValue — presence is modelled
     * distinctly from an absent PLAC (which leaves the event's place NULL).
     */
    #[Test]
    public function anEmptyPlaceLineYieldsANonNullEmptyPlaceValue(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n"
            . "0 @I1@ INDI\n1 BIRT\n2 PLAC\n1 DEAT\n"
            . "0 TRLR\n"
        );

        $birth = $document->individuals[0]->birt[0];
        self::assertInstanceOf(PlaceValue::class, $birth->plac);
        self::assertSame([], $birth->plac->levels);

        // An event without any PLAC line leaves the place absent (NULL), unlike the empty one above.
        self::assertNull($document->individuals[0]->deat[0]->plac);
    }

    /**
     * A PLAC may carry NOTE/FONE/ROMN substructures in 5.5.1; these are intentionally not modelled
     * on PlaceValue (only the name, FORM hierarchy and MAP coordinates are). Such a place must still
     * parse without error, exposing its modelled fields.
     */
    #[Test]
    public function placeSubstructuresBeyondFormAndMapAreNotModelled(): void
    {
        $document = $this->read(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n"
            . "0 @I1@ INDI\n1 BIRT\n2 PLAC Berlin\n3 NOTE A note about the place.\n"
            . "0 TRLR\n"
        );

        $place = $document->individuals[0]->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        // The unmodelled NOTE substructure must not corrupt the name parsing.
        self::assertSame(['Berlin'], $place->levels);
    }

    /**
     * Every bundled GEDCOM fixture is read by the auto-detecting reader without raising an
     * exception, pinning the typed pipeline's parity with the real-world sample files (in
     * particular the bare, name-less SUBM records some of them carry).
     *
     * @param string $file The absolute path to the GEDCOM fixture.
     */
    #[Test]
    #[DataProvider('fixtureProvider')]
    public function readsEveryBundledFixtureWithoutError(string $file): void
    {
        // The read() call throws on any malformed record, so reaching the end without an exception
        // is the assertion; the return type already guarantees a GedcomDocument.
        $this->expectNotToPerformAssertions();

        $reader = GedcomDocumentReader::create(
            self::STANDARD_RECORD_CLASSES,
            dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'
        );

        $reader->read((new StreamFactory())->createStreamFromFile($file));
    }

    /**
     * Provides every bundled GEDCOM fixture.
     *
     * @return array<string, array{0: string}>
     */
    public static function fixtureProvider(): array
    {
        $cases = [];
        $files = glob(dirname(__DIR__) . '/files/*.ged');

        foreach ($files === false ? [] : $files as $file) {
            $cases[basename($file)] = [$file];
        }

        return $cases;
    }

    /**
     * Reads a GEDCOM string through a rewound in-memory stream with the standard record-class map.
     *
     * @param string $gedcom The GEDCOM source to read.
     *
     * @return GedcomDocument The populated aggregate.
     */
    private function read(string $gedcom): GedcomDocument
    {
        $reader = GedcomDocumentReader::create(
            self::STANDARD_RECORD_CLASSES,
            dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'
        );

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return $reader->read($stream);
    }
}
