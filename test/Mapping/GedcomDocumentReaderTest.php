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
        $reader = GedcomDocumentReader::create(
            self::STANDARD_RECORD_CLASSES,
            dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'
        );

        $document = $reader->read((new StreamFactory())->createStreamFromFile($file));

        self::assertInstanceOf(GedcomDocument::class, $document);
    }

    /**
     * Provides every bundled GEDCOM fixture.
     *
     * @return array<string, array{0: string}>
     */
    public static function fixtureProvider(): array
    {
        $cases = [];

        foreach (glob(dirname(__DIR__) . '/files/*.ged') as $file) {
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
