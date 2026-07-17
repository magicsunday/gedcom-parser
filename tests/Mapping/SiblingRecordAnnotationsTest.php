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
use MagicSunday\Gedcom\Mapping\RecordStream;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\MultimediaFile;
use MagicSunday\Gedcom\Model\MultimediaRecord;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\NoteRecord;
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\SubmitterRecord;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * The remaining level-0 records now type their cross-cutting annotation tags — the record-level
 * notes (`NOTE`) that every record carries, and the source citations (`SOUR`) that multimedia and
 * shared-note records carry — as typed {@see Note} and {@see SourceCitation} lists rather than
 * leaving them on `$unknown` (#132, additive roll-out). All reuse the classes the substructures
 * already use and are lossless in both GEDCOM 5.5.1 and 7.0.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(MultimediaRecord::class)]
#[CoversClass(SourceRecord::class)]
#[CoversClass(RepositoryRecord::class)]
#[CoversClass(SubmitterRecord::class)]
#[CoversClass(NoteRecord::class)]
#[UsesClass(Parser::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(Reader::class)]
#[UsesClass(GedcomDocumentReader::class)]
#[UsesClass(GedcomVersionDetector::class)]
#[UsesClass(RecordStream::class)]
#[UsesClass(GedcomTreeReader::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(GedcomObjectMapper::class)]
#[UsesClass(JsonMapperFactory::class)]
#[UsesClass(RegistrySchemaLoader::class)]
#[UsesClass(Schema::class)]
#[UsesClass(GedcomVersion::class)]
#[UsesClass(GedcomDocument::class)]
#[UsesClass(Note::class)]
#[UsesClass(SourceCitation::class)]
#[UsesClass(MultimediaFile::class)]
#[UsesClass(RawSubstructure::class)]
class SiblingRecordAnnotationsTest extends TestCase
{
    /**
     * A multimedia record types both its notes and its source citations; an unmodelled extension tag
     * (`_CUSTOM`) stays on `$unknown`.
     */
    #[Test]
    public function typesAMultimediaRecordsNotesAndSources(): void
    {
        $media = $this->parse(
            "0 @O1@ OBJE\n1 FILE http://example.test/portrait.jpg\n2 FORM jpg\n"
            . "1 NOTE a media note\n1 SOUR a cited source\n1 _CUSTOM media-1\n0 TRLR\n"
        )->multimedia[0];

        self::assertCount(1, $media->note);
        self::assertSame('a media note', $media->note[0]->value);
        self::assertCount(1, $media->sour);
        self::assertSame('a cited source', $media->sour[0]->value);
        self::assertSame(['_CUSTOM'], $this->tags($media->unknown));
    }

    /**
     * The multimedia record types its notes and sources identically under GEDCOM 7.0.
     */
    #[Test]
    public function typesAMultimediaRecordsNotesAndSourcesUnderGedcom70(): void
    {
        $media = $this->parse(
            "0 @O1@ OBJE\n1 FILE http://example.test/portrait.jpg\n2 FORM image/jpeg\n"
            . "1 NOTE a media note\n1 SOUR @S1@\n2 PAGE p. 3\n"
            . "0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n",
            '7.0'
        )->multimedia[0];

        self::assertCount(1, $media->note);
        self::assertSame('a media note', $media->note[0]->value);
        self::assertCount(1, $media->sour);
        self::assertSame('p. 3', $media->sour[0]->page);
        self::assertSame([], $this->tags($media->unknown));
    }

    /**
     * A source record types its record-level notes; an unmodelled extension tag (`_CUSTOM`) stays on
     * `$unknown`.
     */
    #[Test]
    public function typesASourceRecordsNotes(): void
    {
        $source = $this->parse(
            "0 @S1@ SOUR\n1 TITL A source\n1 NOTE a source note\n1 _CUSTOM source-1\n0 TRLR\n"
        )->sources[0];

        self::assertCount(1, $source->note);
        self::assertSame('a source note', $source->note[0]->value);
        self::assertSame(['_CUSTOM'], $this->tags($source->unknown));
    }

    /**
     * A repository record types its record-level notes.
     */
    #[Test]
    public function typesARepositoryRecordsNotes(): void
    {
        $repository = $this->parse(
            "0 @R1@ REPO\n1 NAME A repository\n1 NOTE a repository note\n0 TRLR\n"
        )->repositories[0];

        self::assertCount(1, $repository->note);
        self::assertSame('a repository note', $repository->note[0]->value);
        self::assertSame([], $this->tags($repository->unknown));
    }

    /**
     * A submitter record types its record-level notes.
     */
    #[Test]
    public function typesASubmitterRecordsNotes(): void
    {
        $submitter = $this->parse(
            "0 @U1@ SUBM\n1 NAME A submitter\n1 NOTE a submitter note\n0 TRLR\n"
        )->submitters[0];

        self::assertCount(1, $submitter->note);
        self::assertSame('a submitter note', $submitter->note[0]->value);
        self::assertSame([], $this->tags($submitter->unknown));
    }

    /**
     * A shared-note record types its source citations (a note may cite its sources), in both the
     * GEDCOM 5.5.1 `NOTE` record and the GEDCOM 7.0 `SNOTE` record.
     */
    #[Test]
    public function typesANoteRecordsSources(): void
    {
        $note = $this->parse(
            "0 @N1@ NOTE A shared note\n1 SOUR a cited source\n0 TRLR\n"
        )->notes[0];

        self::assertCount(1, $note->sour);
        self::assertSame('a cited source', $note->sour[0]->value);
        self::assertSame([], $this->tags($note->unknown));

        $note70 = $this->parse(
            "0 @N1@ SNOTE A shared note\n1 SOUR a cited source\n0 TRLR\n",
            '7.0'
        )->notes[0];

        self::assertCount(1, $note70->sour);
        self::assertSame('a cited source', $note70->sour[0]->value);
        self::assertSame([], $this->tags($note70->unknown));
    }

    /**
     * Collects the tags of the preserved substructures.
     *
     * @param list<RawSubstructure> $unknown The preserved substructures.
     *
     * @return list<string> The tags.
     */
    private function tags(array $unknown): array
    {
        return array_map(static fn (RawSubstructure $s): string => $s->tag, $unknown);
    }

    /**
     * Parses the given GEDCOM records into the document.
     *
     * @param string $body    The GEDCOM records.
     * @param string $version The GEDCOM version to declare in the header.
     *
     * @return GedcomDocument The parsed document.
     */
    private function parse(string $body, string $version = '5.5.1'): GedcomDocument
    {
        $charset = $version === '5.5.1' ? "1 CHAR ASCII\n" : '';
        $gedcom  = "0 HEAD\n1 GEDC\n2 VERS " . $version . "\n" . $charset . $body;

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
