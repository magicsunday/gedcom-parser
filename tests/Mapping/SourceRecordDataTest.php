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
use MagicSunday\Gedcom\Model\EventDetail;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitationData;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceDataEvent;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceRecordData;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\CalendarDate;
use MagicSunday\Gedcom\ValueObject\DateType;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_map;
use function file_get_contents;

/**
 * The source record now types its data block (`DATA`) — the events the source records, the agency
 * responsible for them and the accompanying notes — as a typed {@see SourceRecordData} rather than
 * leaving it on the record's `$unknown` (#132, #168).
 *
 * The block exists in both GEDCOM versions. Each recorded event ({@see SourceDataEvent}) carries the
 * list of event types the source covers, together with the date period and place they were recorded
 * for. GEDCOM 7.0 additionally permits shared-note pointers on the block.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(SourceRecordData::class)]
#[CoversClass(SourceDataEvent::class)]
#[CoversClass(SourceRecord::class)]
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
#[UsesClass(IndividualRecord::class)]
#[UsesClass(EventDetail::class)]
#[UsesClass(SourceCitation::class)]
#[UsesClass(SourceCitationData::class)]
#[UsesClass(DateValue::class)]
#[UsesClass(CalendarDate::class)]
#[UsesClass(DateType::class)]
#[UsesClass(PlaceValue::class)]
#[UsesClass(RawSubstructure::class)]
class SourceRecordDataTest extends TestCase
{
    /**
     * A GEDCOM 7.0 source types its data block with the recorded events, the agency and the notes.
     * The recorded date is a period bounding the records, and the place keeps its jurisdictions.
     */
    #[Test]
    public function typesTheSourceDataBlock(): void
    {
        $source = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 AGNC The registry office\n2 EVEN BIRT, DEAT\n"
            . "3 DATE FROM 1900 TO 1950\n4 PHRASE the registry's own wording\n"
            . "3 PLAC Boston, Suffolk, Massachusetts\n2 NOTE A recorded note\n0 TRLR\n",
            '7.0'
        )->sources[0];

        $data = $source->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame('The registry office', $data->agnc);
        self::assertCount(1, $data->even);
        self::assertSame('BIRT, DEAT', $data->even[0]->value);

        $date = $data->even[0]->date;
        self::assertInstanceOf(DateValue::class, $date);
        self::assertSame(DateType::FromTo, $date->type, 'A recorded-event DATE is a period, not an exact date.');
        self::assertSame(1900, $date->date?->year);
        self::assertSame(1950, $date->endDate?->year);
        self::assertSame("the registry's own wording", $date->phrase);

        self::assertSame(['Boston', 'Suffolk', 'Massachusetts'], $data->even[0]->plac?->levels);

        self::assertCount(1, $data->note);
        self::assertSame('A recorded note', $data->note[0]->value);

        self::assertSame([], $this->tags($source->unknown));
        self::assertSame([], $this->tags($data->unknown));
    }

    /**
     * The data block types under GEDCOM 5.5.1 too, where a note appears both inline and as a
     * pointer, and both collapse onto the same typed list.
     */
    #[Test]
    public function typesA551SourceDataBlock(): void
    {
        $source = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 AGNC A parish\n2 EVEN BIRT\n3 DATE FROM 1850 TO 1899\n"
            . "3 PLAC Salem, Essex\n2 NOTE An inline note\n2 NOTE @N1@\n0 TRLR\n",
            '5.5.1'
        )->sources[0];

        $data = $source->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame('A parish', $data->agnc);
        self::assertSame('BIRT', $data->even[0]->value);
        self::assertSame(DateType::FromTo, $data->even[0]->date?->type);
        self::assertSame(['Salem', 'Essex'], $data->even[0]->plac?->levels);
        self::assertCount(2, $data->note, 'The inline and the pointer note form share one typed list.');
        self::assertSame([], $this->tags($source->unknown));
        self::assertSame([], $this->tags($data->unknown));
    }

    /**
     * Several recorded-event blocks repeat, each keeping its own place, and a block without a date
     * leaves it unset rather than borrowing a sibling's.
     */
    #[Test]
    public function typesRepeatedRecordedEvents(): void
    {
        $source = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 EVEN BIRT\n3 PLAC Boston\n2 EVEN DEAT\n3 PLAC Salem\n0 TRLR\n",
            '7.0'
        )->sources[0];

        $data = $source->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertCount(2, $data->even);
        self::assertSame('BIRT', $data->even[0]->value);
        self::assertSame('Boston', $data->even[0]->plac?->raw);
        self::assertNull($data->even[0]->date, 'A recorded-event block without a DATE has none.');
        self::assertSame('DEAT', $data->even[1]->value);
        self::assertSame('Salem', $data->even[1]->plac?->raw);
    }

    /**
     * A GEDCOM 7.0 shared-note pointer on the block is typed, and a block carrying nothing else
     * leaves its remaining members empty rather than synthesising them.
     */
    #[Test]
    public function typesTheSharedNotePointer(): void
    {
        $data = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 SNOTE @N1@\n0 TRLR\n",
            '7.0'
        )->sources[0]->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame(['N1'], $data->snote);
        self::assertSame([], $data->even, 'A block without EVEN yields no recorded-event blocks.');
        self::assertNull($data->agnc);
        self::assertSame([], $data->note);
    }

    /**
     * The shared-note pointer is a GEDCOM 7.0 addition: under GEDCOM 5.5.1 the schema does not
     * permit it on the block, so it is preserved verbatim rather than typed.
     */
    #[Test]
    public function doesNotTypeTheSharedNotePointerUnderGedcom551(): void
    {
        $data = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 SNOTE @N1@\n0 TRLR\n",
            '5.5.1'
        )->sources[0]->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame([], $data->snote);
        self::assertSame(['SNOTE'], $this->tags($data->unknown));
    }

    /**
     * The record-level block and a citation's own block share the `DATA` tag but are distinct
     * structures, so each types into its own model rather than being resolved by tag alone.
     *
     * Asserted under GEDCOM 5.5.1, the version in which both blocks currently type; the citation
     * block does not yet type under GEDCOM 7.0 (see issue #180).
     */
    #[Test]
    public function distinguishesTheRecordBlockFromACitationBlock(): void
    {
        $document = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 EVEN BIRT\n0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n"
            . "3 DATA\n4 TEXT A transcribed line\n0 TRLR\n",
            '5.5.1'
        );

        $recordData   = $document->sources[0]->data;
        $citationData = $document->individuals[0]->birt[0]->sour[0]->data;

        self::assertInstanceOf(SourceRecordData::class, $recordData);
        self::assertSame('BIRT', $recordData->even[0]->value);

        self::assertInstanceOf(SourceCitationData::class, $citationData);
        self::assertSame(['A transcribed line'], $citationData->text);
    }

    /**
     * An out-of-schema substructure beneath the block is preserved verbatim rather than dropped.
     */
    #[Test]
    public function preservesAnUnknownSubstructureBeneathTheBlock(): void
    {
        $source = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 _CUSTOM Extension payload\n2 EVEN BIRT\n3 _NESTED Nested payload\n0 TRLR\n",
            '7.0'
        )->sources[0];

        $data = $source->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame(['_CUSTOM'], $this->tags($data->unknown));
        self::assertSame('Extension payload', $data->unknown[0]->value);
        self::assertSame(['_NESTED'], $this->tags($data->even[0]->unknown));
    }

    /**
     * An extension beneath a note of the block survives. A GEDCOM 5.5.1 note declares no
     * substructures of its own, so typing the block must not reduce its notes to bare strings and
     * discard whatever hangs below them — before the block was typed, the whole subtree was kept.
     */
    #[Test]
    public function preservesAnExtensionBeneathANoteOfTheBlock(): void
    {
        $data = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 NOTE An inline note\n3 _CUSTOM Extension payload\n0 TRLR\n",
            '5.5.1'
        )->sources[0]->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertCount(1, $data->note);
        self::assertSame('An inline note', $data->note[0]->value);
        self::assertSame(['_CUSTOM'], $this->tags($data->note[0]->unknown));
        self::assertSame('Extension payload', $data->note[0]->unknown[0]->value);
    }

    /**
     * A note given as a pointer keeps that pointer even when it carries an extension child, which
     * makes it shape as an object rather than arrive as a bare string.
     */
    #[Test]
    public function preservesAPointerNoteCarryingAnExtension(): void
    {
        $data = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 NOTE @N1@\n3 _CUSTOM Extension payload\n0 TRLR\n",
            '5.5.1'
        )->sources[0]->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame('N1', $data->note[0]->value, 'The shared-note pointer survives the object shape.');
        self::assertSame(['_CUSTOM'], $this->tags($data->note[0]->unknown));
    }

    /**
     * An extension beneath a plainly-valued child of the block survives. Such a child keeps its bare
     * payload and has nowhere to carry substructures, so its subtree is preserved on the block
     * itself — typing a container must never cost what the untyped container kept.
     */
    #[Test]
    public function preservesAnExtensionBeneathAPlainlyValuedChild(): void
    {
        $data = $this->parse(
            "0 @S1@ SOUR\n1 DATA\n2 AGNC Some agency\n3 _CUSTOM Extension payload\n0 TRLR\n",
            '5.5.1'
        )->sources[0]->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame('Some agency', $data->agnc);
        self::assertSame(['AGNC'], $this->tags($data->unknown));
        self::assertSame(['_CUSTOM'], $this->tags($data->unknown[0]->children));
        self::assertSame(
            'Some agency',
            $data->unknown[0]->value,
            'The carrier names the occurrence its unconsumed descendants belong to.'
        );
    }

    /**
     * The schema gives the block no line value of its own, but a file that writes one anyway keeps
     * it rather than losing the whole block.
     */
    #[Test]
    public function toleratesALineValueOnTheBlock(): void
    {
        $data = $this->parse(
            "0 @S1@ SOUR\n1 DATA legacy payload\n2 AGNC An agency\n0 TRLR\n",
            '5.5.1'
        )->sources[0]->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame('legacy payload', $data->value);
        self::assertSame('An agency', $data->agnc, 'The rest of the block still types.');
    }

    /**
     * The block types out of the shipped conformance corpus, whose source record carries the
     * real-world shape: repeated event blocks, a multi-type event list and a continued note.
     */
    #[Test]
    public function typesTheDataBlockOfTheConformanceCorpus(): void
    {
        $path = __DIR__ . '/../files/allged.ged';
        self::assertFileExists($path);

        $stream = (new StreamFactory())->createStream((string) file_get_contents($path));
        $stream->rewind();

        $data = (new Parser($stream))->parse()->sources[0]->data;

        self::assertInstanceOf(SourceRecordData::class, $data);
        self::assertSame('Resposible agency', $data->agnc);

        self::assertCount(2, $data->even, 'The corpus source records a birth/christening and a death block.');
        self::assertSame('BIRT, CHR', $data->even[0]->value, 'One block covers several event types at once.');
        self::assertSame('DEAT', $data->even[1]->value);

        // The corpus is the only case carrying a full day-and-month period rather than bare years.
        $date = $data->even[0]->date;
        self::assertInstanceOf(DateValue::class, $date);
        self::assertSame(DateType::FromTo, $date->type);
        self::assertInstanceOf(CalendarDate::class, $date->date);
        self::assertSame(1, $date->date->day);
        self::assertSame(1, $date->date->month);
        self::assertSame(1980, $date->date->year);

        $endDate = $date->endDate;
        self::assertInstanceOf(CalendarDate::class, $endDate);
        self::assertSame(1, $endDate->day);
        self::assertSame(2, $endDate->month, 'The end month (FEB) differs from the start month.');
        self::assertSame(1982, $endDate->year);

        self::assertCount(1, $data->note);
        self::assertSame(
            "A note about whatever\nNote continued here. The word TEST should not be broken!",
            $data->note[0]->value,
            'CONT starts a new line while CONC continues one without inserting a space.'
        );

        self::assertSame([], $this->tags($data->unknown));
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
    private function parse(string $body, string $version): GedcomDocument
    {
        $charset = $version === '5.5.1' ? "1 CHAR ASCII\n" : '';
        $gedcom  = "0 HEAD\n1 GEDC\n2 VERS " . $version . "\n" . $charset . $body;

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
