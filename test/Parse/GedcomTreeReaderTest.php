<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Parse;

use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the generic GEDCOM tree builder that nests the flat reader lines into an
 * immutable node tree, one level-0 record at a time.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomNode::class)]
#[CoversClass(GedcomTreeReader::class)]
#[UsesClass(Reader::class)]
class GedcomTreeReaderTest extends TestCase
{
    /**
     * Builds a tree reader over an in-memory GEDCOM string.
     */
    private static function readerFor(string $gedcom): GedcomTreeReader
    {
        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return new GedcomTreeReader(new Reader($stream));
    }

    /**
     * A level-0 record with its immediate substructures becomes a node with children,
     * each carrying its own tag and value.
     */
    #[Test]
    public function readRecordNestsTheImmediateSubstructures(): void
    {
        $reader = self::readerFor("0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n0 TRLR\n");

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertSame(0, $node->level);
        self::assertSame('INDI', $node->tag);
        self::assertSame('I1', $node->identifier);
        self::assertNull($node->value);
        self::assertCount(2, $node->children);

        self::assertSame(1, $node->children[0]->level);
        self::assertSame('NAME', $node->children[0]->tag);
        self::assertSame('John /Doe/', $node->children[0]->value);
        self::assertSame('SEX', $node->children[1]->tag);
        self::assertSame('M', $node->children[1]->value);
    }

    /**
     * Substructures nest to arbitrary depth: a BIRT event carries its own DATE and PLAC.
     */
    #[Test]
    public function readRecordNestsToArbitraryDepth(): void
    {
        $reader = self::readerFor(
            "0 @I1@ INDI\n1 BIRT\n2 DATE 1 JAN 2000\n2 PLAC Berlin\n1 DEAT\n2 DATE 2050\n0 TRLR\n"
        );

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertCount(2, $node->children);

        $birth = $node->children[0];
        self::assertSame('BIRT', $birth->tag);
        self::assertNull($birth->value);
        self::assertCount(2, $birth->children);
        self::assertSame('DATE', $birth->children[0]->tag);
        self::assertSame('1 JAN 2000', $birth->children[0]->value);
        self::assertSame('PLAC', $birth->children[1]->tag);
        self::assertSame('Berlin', $birth->children[1]->value);

        self::assertSame(2, $birth->children[0]->level, 'the nested DATE keeps its level-2 number');

        $death = $node->children[1];
        self::assertSame('DEAT', $death->tag);
        self::assertCount(1, $death->children);
        self::assertSame('2050', $death->children[0]->value);
    }

    /**
     * The version-agnostic builder nests purely by level number and applies no grammar rules,
     * so a malformed level jump (0 → 2 with no intervening level 1) still nests by depth while
     * the node retains the raw level number for the schema layer to validate later.
     */
    #[Test]
    public function readRecordRetainsTheRawLevelOnAMalformedLevelJump(): void
    {
        $reader = self::readerFor("0 @I1@ INDI\n2 DATE 2000\n0 TRLR\n");

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertSame(0, $node->level);
        self::assertCount(1, $node->children, 'the level-2 line still nests directly under the record');

        $date = $node->children[0];
        self::assertSame('DATE', $date->tag);
        self::assertSame(2, $date->level, 'the illegal 0 → 2 jump is preserved as the raw level');
    }

    /**
     * A substructure subtree that runs straight into end-of-stream, with no trailing level-0
     * record, is closed by the reader returning FALSE rather than by a sibling push-back.
     */
    #[Test]
    public function readRecordClosesASubtreeOnEndOfStream(): void
    {
        $reader = self::readerFor("0 @I1@ INDI\n1 NAME John /Doe/\n");

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertSame('INDI', $node->tag);
        self::assertCount(1, $node->children, 'the NAME subtree closes on end-of-stream, not on a sibling');
        self::assertSame('NAME', $node->children[0]->tag);

        self::assertNull($reader->readRecord(), 'the stream is exhausted with no trailing record');
    }

    /**
     * A cross-reference pointer value is exposed as the node's xref, not its text value.
     */
    #[Test]
    public function readRecordExposesAPointerValueAsXref(): void
    {
        $reader = self::readerFor("0 @F1@ FAM\n1 HUSB @I1@\n0 TRLR\n");

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertSame('FAM', $node->tag);
        self::assertSame('F1', $node->identifier);

        $husband = $node->children[0];
        self::assertSame('HUSB', $husband->tag);
        self::assertSame('I1', $husband->xref);
        self::assertNull($husband->value);
    }

    /**
     * The reader streams one level-0 record per call and returns NULL at end of stream.
     */
    #[Test]
    public function readRecordStreamsOneRecordPerCallThenNull(): void
    {
        $reader = self::readerFor("0 HEAD\n1 SOUR X\n0 @I1@ INDI\n0 TRLR\n");

        $head = $reader->readRecord();
        self::assertInstanceOf(GedcomNode::class, $head);
        self::assertSame('HEAD', $head->tag);
        self::assertCount(1, $head->children);
        self::assertSame('SOUR', $head->children[0]->tag);
        self::assertSame('X', $head->children[0]->value);

        $individual = $reader->readRecord();
        self::assertInstanceOf(GedcomNode::class, $individual);
        self::assertSame('INDI', $individual->tag);
        self::assertSame('I1', $individual->identifier);
        self::assertSame([], $individual->children);

        $trailer = $reader->readRecord();
        self::assertInstanceOf(GedcomNode::class, $trailer);
        self::assertSame('TRLR', $trailer->tag);

        self::assertNull($reader->readRecord(), 'end of stream yields NULL');
    }

    /**
     * An empty stream yields NULL on the first read.
     */
    #[Test]
    public function readRecordReturnsNullOnAnEmptyStream(): void
    {
        self::assertNull(self::readerFor('')->readRecord());
    }

    /**
     * A CONC continuation reassembles a value split across physical lines with no separator, so
     * the continuation lines fold into the superstructure's value instead of becoming children.
     */
    #[Test]
    public function readRecordConcatenatesConcContinuationWithoutABreak(): void
    {
        $reader = self::readerFor("0 @S1@ SOUR\n1 TITL A very long\n2 CONC  source title\n0 TRLR\n");

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertCount(1, $node->children, 'the CONC line folds into the title, it is not a child');
        self::assertSame('TITL', $node->children[0]->tag);
        self::assertSame('A very long source title', $node->children[0]->value);
        self::assertSame([], $node->children[0]->children, 'the CONC line is not exposed as a child node');
    }

    /**
     * A CONT continuation reassembles a value split across physical lines with a line break, so
     * each CONT contributes a newline plus its text.
     */
    #[Test]
    public function readRecordJoinsContContinuationWithANewline(): void
    {
        $reader = self::readerFor("0 @S1@ SOUR\n1 TEXT Line one\n2 CONT Line two\n2 CONT Line three\n0 TRLR\n");

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertCount(1, $node->children);
        self::assertSame('TEXT', $node->children[0]->tag);
        self::assertSame("Line one\nLine two\nLine three", $node->children[0]->value);
    }

    /**
     * CONC/CONT continuations fold into their superstructure's value while genuine substructures
     * on the same node still nest as children, and the folding recurses to any depth.
     */
    #[Test]
    public function readRecordFoldsContinuationsButKeepsRealSubstructures(): void
    {
        $reader = self::readerFor(
            "0 @I1@ INDI\n1 NOTE A long\n2 CONC er note\n2 CONT on two lines\n2 SOUR @S1@\n"
            . "1 BIRT\n2 PLAC A split\n3 CONC  place\n0 TRLR\n"
        );

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertCount(2, $node->children);

        $note = $node->children[0];
        self::assertSame('NOTE', $note->tag);
        self::assertSame("A longer note\non two lines", $note->value);
        self::assertCount(1, $note->children, 'only the real SOUR substructure remains a child');
        self::assertSame('SOUR', $note->children[0]->tag);
        self::assertSame('S1', $note->children[0]->xref);

        $place = $node->children[1]->children[0];
        self::assertSame('PLAC', $place->tag);
        self::assertSame('A split place', $place->value, 'a deeper CONC folds into the nested PLAC value');
    }

    /**
     * Blank lines inside a record and a trailing blank after the trailer do not create phantom
     * nodes or a spurious extra record: the underlying reader skips them (GH-41), so the tree
     * builder only ever sees real, level-tagged lines.
     */
    #[Test]
    public function readRecordIsUnaffectedByBlankLines(): void
    {
        $reader = self::readerFor("0 @I1@ INDI\n1 NAME John /Doe/\n\n1 SEX M\n0 TRLR\n\n");

        $node = $reader->readRecord();

        self::assertInstanceOf(GedcomNode::class, $node);
        self::assertSame('INDI', $node->tag);
        self::assertCount(2, $node->children, 'the blank line between NAME and SEX must not add a phantom child');
        self::assertSame('NAME', $node->children[0]->tag);
        self::assertSame('SEX', $node->children[1]->tag);

        $trailer = $reader->readRecord();
        self::assertInstanceOf(GedcomNode::class, $trailer);
        self::assertSame('TRLR', $trailer->tag);

        self::assertNull($reader->readRecord(), 'the trailing blank line must not become a phantom record');
    }
}
