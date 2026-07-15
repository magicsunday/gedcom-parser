<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\TypedModel;

use MagicSunday\Gedcom\Mapping\GedcomObjectMapper;
use MagicSunday\Gedcom\Mapping\JsonMapperFactory;
use MagicSunday\Gedcom\Mapping\TypedGedcomParser;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\TypedModel\EventDetail;
use MagicSunday\Gedcom\TypedModel\FamilyRecord;
use MagicSunday\Gedcom\TypedModel\GedcomDocument;
use MagicSunday\Gedcom\TypedModel\IndividualRecord;
use MagicSunday\Gedcom\TypedModel\MultimediaRecord;
use MagicSunday\Gedcom\TypedModel\NoteRecord;
use MagicSunday\Gedcom\TypedModel\RepositoryRecord;
use MagicSunday\Gedcom\TypedModel\SourceRecord;
use MagicSunday\Gedcom\TypedModel\SubmitterRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * Tests the eager typed aggregate: records are grouped by their modelled type, document order is
 * preserved within each group, an unmodelled record lands in the others bucket, and the streaming
 * parser's parseDocument() convenience aggregates end-to-end.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomDocument::class)]
#[CoversClass(TypedGedcomParser::class)]
#[UsesClass(GedcomObjectMapper::class)]
#[UsesClass(JsonMapperFactory::class)]
#[UsesClass(GedcomTreeReader::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(Reader::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(RegistrySchemaLoader::class)]
#[UsesClass(Schema::class)]
#[UsesClass(GedcomVersion::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(FamilyRecord::class)]
#[UsesClass(SourceRecord::class)]
#[UsesClass(NoteRecord::class)]
#[UsesClass(RepositoryRecord::class)]
#[UsesClass(MultimediaRecord::class)]
#[UsesClass(SubmitterRecord::class)]
#[UsesClass(EventDetail::class)]
class GedcomDocumentTest extends TestCase
{
    /**
     * Each record is filed under the list of its modelled type, and a record whose type is not one
     * of the modelled records is kept in the others bucket rather than dropped.
     */
    #[Test]
    public function fromRecordsGroupsEachRecordByItsModelledType(): void
    {
        $individual = new IndividualRecord('I1');
        $family     = new FamilyRecord('F1');
        $source     = new SourceRecord('S1');
        $note       = new NoteRecord('N1');
        $repository = new RepositoryRecord('R1', 'Archive');
        $multimedia = new MultimediaRecord('O1');
        $submitter  = new SubmitterRecord('U1', 'Jane Doe');
        $other      = new EventDetail();

        $document = GedcomDocument::fromRecords(
            [$individual, $family, $source, $note, $repository, $multimedia, $submitter, $other]
        );

        self::assertSame([$individual], $document->individuals);
        self::assertSame([$family], $document->families);
        self::assertSame([$source], $document->sources);
        self::assertSame([$note], $document->notes);
        self::assertSame([$repository], $document->repositories);
        self::assertSame([$multimedia], $document->multimedia);
        self::assertSame([$submitter], $document->submitters);
        self::assertSame([$other], $document->others, 'an unmodelled record is preserved, not dropped');
    }

    /**
     * Records of the same type keep their document order within their group.
     */
    #[Test]
    public function fromRecordsPreservesDocumentOrderWithinAGroup(): void
    {
        $first  = new IndividualRecord('I1');
        $second = new IndividualRecord('I2');

        $document = GedcomDocument::fromRecords([$first, $second]);

        self::assertSame([$first, $second], $document->individuals);
    }

    /**
     * An empty stream of records yields an aggregate whose every group is an empty list.
     */
    #[Test]
    public function fromRecordsOnAnEmptyIterableYieldsEmptyGroups(): void
    {
        $document = GedcomDocument::fromRecords([]);

        self::assertSame([], $document->individuals);
        self::assertSame([], $document->families);
        self::assertSame([], $document->sources);
        self::assertSame([], $document->notes);
        self::assertSame([], $document->repositories);
        self::assertSame([], $document->multimedia);
        self::assertSame([], $document->submitters);
        self::assertSame([], $document->others);
    }

    /**
     * The streaming parser's parseDocument() convenience drains its generator into the typed
     * aggregate, so an INDI and a FAM in the stream land in their respective groups.
     */
    #[Test]
    public function parseDocumentAggregatesTheStreamsRecords(): void
    {
        $parser = TypedGedcomParser::create(
            GedcomVersion::V551,
            [
                'INDI' => IndividualRecord::class,
                'FAM'  => FamilyRecord::class,
            ],
            dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'
        );

        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 SEX M\n0 @F1@ FAM\n1 HUSB @I1@\n0 TRLR\n"
        );
        $stream->rewind();

        $document = $parser->parseDocument($stream);

        self::assertCount(1, $document->individuals);
        self::assertSame('I1', $document->individuals[0]->xref);
        self::assertCount(1, $document->families);
        self::assertSame('F1', $document->families[0]->xref);
        self::assertSame([], $document->sources, 'a record type absent from the stream is an empty group');
    }
}
