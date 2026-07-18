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
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * A substructure the schema allows once but the file gives more than once is preserved verbatim
 * rather than discarded (#205).
 *
 * The shaping assigns a non-collection property only while it is unset, so every later occurrence
 * used to fall out of the loop with nothing done to it. That made a repeated recognised tag the one
 * path where content vanished outright: the preservation beside it catches tags the schema does not
 * recognise, not a recognised one appearing too often.
 *
 * A repeated occurrence is malformed input, which is precisely what the preservation exists for —
 * everything the file said survives, either typed or verbatim. The first occurrence still maps to
 * the typed property, so nothing about the well-formed case changes.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomObjectMapper::class)]
#[UsesClass(JsonMapperFactory::class)]
#[UsesClass(Parser::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(Reader::class)]
#[UsesClass(GedcomDocumentReader::class)]
#[UsesClass(GedcomVersionDetector::class)]
#[UsesClass(RecordStream::class)]
#[UsesClass(GedcomTreeReader::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(RegistrySchemaLoader::class)]
#[UsesClass(Schema::class)]
#[UsesClass(GedcomVersion::class)]
#[UsesClass(GedcomDocument::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(FamilyRecord::class)]
#[UsesClass(RawSubstructure::class)]
class RepeatedSubstructurePreservationTest extends TestCase
{
    /**
     * A repeated single-cardinality leaf keeps its first occurrence typed and every later one
     * verbatim.
     *
     * @param string $version The GEDCOM version to parse it under.
     */
    #[Test]
    #[DataProvider('gedcomVersions')]
    public function preservesARepeatedLeafRatherThanDiscardingIt(string $version): void
    {
        $individual = $this->parse("0 @I1@ INDI\n1 SEX M\n1 SEX F\n", $version)->individuals[0];

        self::assertSame('M', $individual->sex, 'The first occurrence still maps.');
        self::assertSame(['SEX'], $this->tags($individual->unknown), 'The second is kept rather than dropped.');
        self::assertSame('F', $individual->unknown[0]->value);
        self::assertSame([], $individual->unknown[0]->children);
    }

    /**
     * A third occurrence is preserved beside the second rather than only the first duplicate being
     * caught, so the preservation is not itself single-cardinality.
     */
    #[Test]
    public function preservesEveryLaterOccurrenceNotOnlyTheFirstDuplicate(): void
    {
        $individual = $this->parse("0 @I1@ INDI\n1 SEX M\n1 SEX F\n1 SEX U\n", '7.0')->individuals[0];

        self::assertSame('M', $individual->sex);
        self::assertSame(['SEX', 'SEX'], $this->tags($individual->unknown));
        self::assertSame('F', $individual->unknown[0]->value);
        self::assertSame('U', $individual->unknown[1]->value);
    }

    /**
     * A repeated occurrence carrying substructures of its own was already preserved before this
     * change, through the carrier that keeps a plain child's subtree. It must be preserved exactly
     * once — the two paths must not both fire and write the line twice.
     *
     * @param string $version The GEDCOM version to parse it under.
     */
    #[Test]
    #[DataProvider('gedcomVersions')]
    public function preservesARepeatedOccurrenceCarryingASubtreeExactlyOnce(string $version): void
    {
        $individual = $this->parse("0 @I1@ INDI\n1 SEX M\n1 SEX F\n2 _SRC a guess\n", $version)->individuals[0];

        self::assertSame('M', $individual->sex);
        self::assertSame(['SEX'], $this->tags($individual->unknown), 'Preserved once, not twice.');
        self::assertSame('F', $individual->unknown[0]->value);
        self::assertSame(['_SRC'], $this->tags($individual->unknown[0]->children));
        self::assertSame('a guess', $individual->unknown[0]->children[0]->value);
    }

    /**
     * The preservation reaches a repeated container too, with its whole subtree — not only a leaf
     * whose payload is a single line.
     *
     * @param string $version The GEDCOM version to parse it under.
     */
    #[Test]
    #[DataProvider('gedcomVersions')]
    public function preservesARepeatedContainerWithItsSubtree(string $version): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 CHAN\n2 DATE 1 JAN 2000\n1 CHAN\n2 DATE 2 FEB 2001\n",
            $version
        )->individuals[0];

        self::assertSame('1 JAN 2000', $individual->chan?->date?->value, 'The first occurrence still maps.');
        self::assertSame(['CHAN'], $this->tags($individual->unknown));

        $preserved = $individual->unknown[0];
        self::assertSame(['DATE'], $this->tags($preserved->children), 'The subtree comes with it.');
        self::assertSame('2 FEB 2001', $preserved->children[0]->value);
    }

    /**
     * The shaping is recursive, so the preservation applies at every level — and the preserved copy
     * must land on the container the repetition sat in, not bubble up to the record.
     *
     * Which container receives it is the whole point: a regression that re-parented the copy would
     * still leave it "preserved somewhere" and pass a test that only counted it.
     */
    #[Test]
    public function preservesARepetitionOnTheNestedContainerItSatIn(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 CHAN\n2 DATE 1 JAN 2000\n2 DATE 2 FEB 2001\n",
            '7.0'
        )->individuals[0];

        $change = $individual->chan;
        self::assertNotNull($change);
        self::assertSame('1 JAN 2000', $change->date?->value, 'The first occurrence still maps.');

        self::assertSame([], $individual->unknown, 'The record itself gains nothing.');
        self::assertSame(['DATE'], $this->tags($change->unknown), 'The copy lands on the change it repeated in.');
        self::assertSame('2 FEB 2001', $change->unknown[0]->value);
    }

    /**
     * A tag the schema allows once but whose MODEL property is a list is reconciled into that list,
     * and must bypass the preservation just as a schema-permitted repetition does.
     *
     * This is the confusable half of the arity test: the repetition IS malformed against the
     * cardinality, yet the list is still where it belongs, so the two conditions guarding the
     * collection branch must both be honoured rather than only the schema one.
     */
    #[Test]
    public function stillCollectsARepetitionReconciledIntoAListProperty(): void
    {
        $family = $this->parse("0 @F1@ FAM\n1 NCHI 2\n1 NCHI 3\n", '5.5.1')->families[0];

        self::assertCount(2, $family->nchi, 'Both reach the list the model declares.');
        self::assertSame([], $family->unknown, 'The list is where it belongs, so nothing is diverted.');
    }

    /**
     * The new diversion fires on a repetition alone: a record whose single-cardinality tags each
     * appear once keeps its `$unknown` empty.
     *
     * This says nothing about well-formed input in general — a recognised child that keeps its plain
     * payload still puts its own subtree on `$unknown` through the carrier path, which predates this
     * change and is pinned by `LeafSubstructurePreservationTest`. The claim here is narrower: with
     * nothing repeated, nothing is diverted.
     */
    #[Test]
    public function divertsNothingWhenNoSubstructureRepeats(): void
    {
        $individual = $this->parse("0 @I1@ INDI\n1 SEX M\n1 CHAN\n2 DATE 1 JAN 2000\n", '7.0')->individuals[0];

        self::assertSame('M', $individual->sex);
        self::assertNotNull($individual->chan);
        self::assertSame([], $individual->unknown, 'Nothing repeats, so nothing is diverted.');
    }

    /**
     * A repeated multi-cardinality tag is collected into its list as it always was, and must not
     * reach the new diversion: the schema permits the repetition, so it is not malformed.
     *
     * The fixture gives each name no substructures of its own, which isolates the branch under test
     * — a list entry that DOES carry children still reaches `$unknown` through the older carrier
     * path, and that is not what this pins.
     */
    #[Test]
    public function stillCollectsARepeatedMultiCardinalityTag(): void
    {
        $individual = $this->parse("0 @I1@ INDI\n1 NAME John /Smith/\n1 NAME Jack /Smith/\n", '7.0')->individuals[0];

        self::assertCount(2, $individual->name, 'Both names reach the list.');
        self::assertSame([], $individual->unknown, 'A permitted repetition is not diverted as a duplicate.');
    }

    /**
     * The GEDCOM versions whose registries define the individual record.
     *
     * @return iterable<string, array{string}> The version, keyed by itself.
     */
    public static function gedcomVersions(): iterable
    {
        yield '7.0' => ['7.0'];
        yield '5.5.1' => ['5.5.1'];
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
        return array_map(static fn (RawSubstructure $entry): string => $entry->tag, $unknown);
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
        $gedcom  = "0 HEAD\n1 GEDC\n2 VERS " . $version . "\n" . $charset . $body . "0 TRLR\n";

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
