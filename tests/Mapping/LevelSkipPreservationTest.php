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
 * A substructure whose level skips one is preserved verbatim rather than dropped (#208).
 *
 * The level number is the only thing in a GEDCOM line that says where it belongs, so a child sitting
 * more than one level below its parent is malformed. Refusing to attribute it to the field it would
 * otherwise have matched is right and stays; dropping it was not. Every other way a child can fail to
 * reach a typed field — an out-of-schema tag, a recognised tag the object does not model, a
 * recognised tag repeated past its cardinality — already diverts to `$unknown`, and this was the last
 * branch of that loop that did not.
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
#[UsesClass(RawSubstructure::class)]
class LevelSkipPreservationTest extends TestCase
{
    /**
     * A level-skipped child of a record is kept on that record, and does not reach the typed field
     * its tag would otherwise have matched.
     *
     * @param string $version The GEDCOM version to parse it under.
     */
    #[Test]
    #[DataProvider('gedcomVersions')]
    public function preservesALevelSkippedChildOfARecord(string $version): void
    {
        // The SEX sits at level 2 directly under the level-0 record, skipping level 1.
        $individual = $this->parse("0 @I1@ INDI\n2 SEX M\n1 NAME John /Smith/\n", $version)->individuals[0];

        self::assertNull($individual->sex, 'A skipped level is not attributed to the field it matches.');
        self::assertCount(1, $individual->name, 'The well-formed sibling is unaffected.');

        self::assertSame(['SEX'], $this->tags($individual->unknown), 'It is kept rather than dropped.');
        self::assertSame('M', $individual->unknown[0]->value);
    }

    /**
     * The preservation reaches a skip nested inside a modelled substructure, landing on that
     * substructure rather than bubbling to the record.
     *
     * @param string $version The GEDCOM version to parse it under.
     */
    #[Test]
    #[DataProvider('gedcomVersions')]
    public function preservesALevelSkippedChildOnTheContainerItSatIn(string $version): void
    {
        // The DATE sits at level 3 under the level-1 birth, skipping level 2.
        $individual = $this->parse("0 @I1@ INDI\n1 BIRT\n3 DATE 1 JAN 1900\n", $version)->individuals[0];

        $birth = $individual->birt[0];
        self::assertNull($birth->date, 'A skipped level is not attributed to the field it matches.');

        self::assertSame([], $individual->unknown, 'The record itself gains nothing.');
        self::assertSame(['DATE'], $this->tags($birth->unknown), 'The copy lands on the event it sat in.');
        self::assertSame('1 JAN 1900', $birth->unknown[0]->value);
    }

    /**
     * A level-skipped child keeps its own subtree, so a skip does not cost the lines beneath it
     * either.
     */
    #[Test]
    public function preservesTheSubtreeOfALevelSkippedChild(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n2 BIRT\n3 DATE 1 JAN 1900\n3 PLAC Boston\n",
            '7.0'
        )->individuals[0];

        self::assertSame([], $individual->birt, 'The skipped birth is not attributed to the record.');
        self::assertSame(['BIRT'], $this->tags($individual->unknown));

        $preserved = $individual->unknown[0];
        self::assertSame(['DATE', 'PLAC'], $this->tags($preserved->children), 'The whole subtree comes with it.');
        self::assertSame('1 JAN 1900', $preserved->children[0]->value);
        self::assertSame('Boston', $preserved->children[1]->value);
    }

    /**
     * The rule is "more than one level below", not "exactly two": a deeper skip is preserved on the
     * same terms, so the general claim rests on more than its boundary case.
     */
    #[Test]
    public function preservesASkipOfMoreThanOneLevel(): void
    {
        $individual = $this->parse("0 @I1@ INDI\n3 SEX M\n", '7.0')->individuals[0];

        self::assertNull($individual->sex);
        self::assertSame(['SEX'], $this->tags($individual->unknown));
        self::assertSame('M', $individual->unknown[0]->value);
    }

    /**
     * A skip and an out-of-schema sibling land on one list without clobbering each other, in the
     * order the file gave them — the preservation branches share `$unknown`, and the container
     * promises document order across all of them.
     */
    #[Test]
    public function keepsASkipAndAnExtensionInDocumentOrderOnOneList(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n2 SEX M\n1 _VEND foo\n1 NAME John /Smith/\n",
            '7.0'
        )->individuals[0];

        self::assertNull($individual->sex);
        self::assertCount(1, $individual->name, 'The well-formed sibling still maps.');
        self::assertSame(['SEX', '_VEND'], $this->tags($individual->unknown), 'Both survive, in file order.');
    }

    /**
     * Two skipped children of the same single-cardinality tag BOTH survive.
     *
     * This is deliberate rather than an oversight, and it is the one place the two malformed-input
     * rules differ: the first-wins rule governs the typed property, and a skipped child never
     * reaches one. With nothing to be first at, there is nothing to lose to a later occurrence.
     */
    #[Test]
    public function preservesEverySkippedOccurrenceOfOneTag(): void
    {
        $individual = $this->parse("0 @I1@ INDI\n2 SEX M\n2 SEX F\n", '7.0')->individuals[0];

        self::assertNull($individual->sex);
        self::assertSame(['SEX', 'SEX'], $this->tags($individual->unknown));
        self::assertSame('M', $individual->unknown[0]->value);
        self::assertSame('F', $individual->unknown[1]->value);
    }

    /**
     * A skip beneath a value-object leaf reaches that leaf's own `$unknown` through its handler,
     * which is a structurally different shape from a constructor-hydrated model: the leaf is shaped
     * without a target class, so the preserved entry travels as a plain key the handler must read.
     */
    #[Test]
    public function preservesALevelSkippedChildBeneathAValueObjectLeaf(): void
    {
        // The PHRASE sits at level 4 under the level-2 date, skipping level 3.
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 DATE 1 JAN 1900\n4 PHRASE around new year\n",
            '7.0'
        )->individuals[0];

        $date = $individual->birt[0]->date;
        self::assertNotNull($date);
        self::assertNull($date->phrase, 'A skipped level is not attributed to the field it matches.');

        self::assertSame(['PHRASE'], $this->tags($date->unknown), 'It lands on the date it sat beneath.');
        self::assertSame('around new year', $date->unknown[0]->value);
    }

    /**
     * A continuation whose level skips reaches the list too, rather than being dropped.
     *
     * It is the awkward member of this class: a `CONC`/`CONT` is a pseudo-structure, not a
     * substructure, and one whose level skips cannot be folded into the line it meant to continue —
     * the fold needs it exactly one level below. Keeping it verbatim is still better than losing the
     * text outright, but it does mean an entry here is not always a substructure of what it sits
     * under, which is pinned so the compromise stays deliberate.
     */
    #[Test]
    public function preservesALevelSkippedContinuationRatherThanDroppingIt(): void
    {
        $name = $this->parse("0 @I1@ INDI\n1 NAME John /Smith/\n3 CONC son\n", '5.5.1')->individuals[0]->name[0];

        self::assertSame('John /Smith/', $name->value, 'The skipped continuation is NOT folded in.');
        self::assertSame(['CONC'], $this->tags($name->unknown), 'It survives rather than being dropped.');
        self::assertSame('son', $name->unknown[0]->value);
    }

    /**
     * A well-formed record gains nothing, so the preservation fires on the skip alone rather than on
     * every child the loop walks.
     */
    #[Test]
    public function divertsNothingWhenNoLevelIsSkipped(): void
    {
        $individual = $this->parse("0 @I1@ INDI\n1 SEX M\n1 BIRT\n2 DATE 1 JAN 1900\n", '7.0')->individuals[0];

        self::assertSame('M', $individual->sex);
        self::assertSame('1 JAN 1900', $individual->birt[0]->date?->raw);
        self::assertSame([], $individual->unknown, 'No level is skipped, so nothing is diverted.');
        self::assertSame([], $individual->birt[0]->unknown);
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
