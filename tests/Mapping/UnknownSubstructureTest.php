<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Mapping;

use MagicSunday\Gedcom\Mapping\GedcomObjectMapper;
use MagicSunday\Gedcom\Mapping\JsonMapperFactory;
use MagicSunday\Gedcom\Model\ChangeDate;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\NoteTranslation;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * The safety net (#132, increment 0): a substructure the typed model does not consume — an
 * extension (`_`-prefixed) tag or any tag not permitted by the schema at its position — is
 * preserved verbatim on the carrying object's `$unknown` list as a {@see RawSubstructure} instead
 * of being silently dropped. Preserved at every object-bearing nesting level, including under a
 * modelled substructure such as `CHAN` and a closure-built `Note`/`NoteTranslation` (a leaf
 * substructure carries no `$unknown` of its own — a documented boundary).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(RawSubstructure::class)]
#[CoversClass(GedcomObjectMapper::class)]
#[UsesClass(Parser::class)]
#[UsesClass(JsonMapperFactory::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(GedcomDocument::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(ChangeDate::class)]
#[UsesClass(Note::class)]
#[UsesClass(NoteTranslation::class)]
class UnknownSubstructureTest extends TestCase
{
    /**
     * An extension tag directly under a record — with its own nested child — is preserved on the
     * record's `$unknown` list as a raw subtree, rather than dropped.
     */
    #[Test]
    public function anExtensionTagUnderARecordIsPreserved(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 NAME John /Doe/\n1 _CUSTOM hello\n2 _SUB nested\n0 TRLR\n";

        $individual = $this->parseIndividual($gedcom);

        $custom = $this->firstUnknown($individual->unknown, '_CUSTOM');

        self::assertInstanceOf(RawSubstructure::class, $custom);
        self::assertSame('_CUSTOM', $custom->tag);
        self::assertSame('hello', $custom->value);
        self::assertCount(1, $custom->children);
        self::assertSame('_SUB', $custom->children[0]->tag);
        self::assertSame('nested', $custom->children[0]->value);
    }

    /**
     * An extension tag nested under a modelled substructure (`CHAN`) is preserved on that
     * substructure's own `$unknown` list — the net is recursive, not only record-level.
     */
    #[Test]
    public function anExtensionTagNestedUnderAModelledSubstructureIsPreserved(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 NAME John /Doe/\n1 CHAN\n2 DATE 1 JAN 2020\n2 _WT_USER admin\n0 TRLR\n";

        $individual = $this->parseIndividual($gedcom);

        self::assertNotNull($individual->chan);

        $wtUser = $this->firstUnknown($individual->chan->unknown, '_WT_USER');

        self::assertInstanceOf(RawSubstructure::class, $wtUser);
        self::assertSame('admin', $wtUser->value);
    }

    /**
     * An extension nested under a closure-built {@see Note} (a `CHAN.NOTE`) is preserved on that
     * note's own `$unknown` list — the note is not hydrated through the mapper's constructor path,
     * so its preservation is a separate wiring that must be pinned independently.
     */
    #[Test]
    public function anExtensionUnderANoteIsPreserved(): void
    {
        // A GEDCOM 7.0 note is a structured substructure (LANG/MIME/TRAN), so the mapper descends
        // into it and the extension beneath it is captured — unlike a 5.5.1 note, which is a leaf.
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 7.0\n"
            . "0 @I1@ INDI\n1 NAME John /Doe/\n1 CHAN\n2 DATE 1 JAN 2020\n2 NOTE a change note\n3 _CUSTOM x\n0 TRLR\n";

        $individual = $this->parseIndividual($gedcom);

        self::assertNotNull($individual->chan);
        self::assertCount(1, $individual->chan->note);

        $custom = $this->firstUnknown($individual->chan->note[0]->unknown, '_CUSTOM');

        self::assertInstanceOf(RawSubstructure::class, $custom);
        self::assertSame('x', $custom->value);
    }

    /**
     * An extension nested under a note translation (`NOTE.TRAN`) is preserved on that translation's
     * own `$unknown` list. The translation is closure-built on a path separate from the note itself,
     * so it is pinned independently.
     */
    #[Test]
    public function anExtensionUnderANoteTranslationIsPreserved(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 7.0\n"
            . "0 @I1@ INDI\n1 NAME John /Doe/\n1 CHAN\n2 DATE 1 JAN 2020\n2 NOTE a change note\n3 TRAN a translation\n4 _CUSTOM x\n0 TRLR\n";

        $individual = $this->parseIndividual($gedcom);

        self::assertNotNull($individual->chan);
        self::assertCount(1, $individual->chan->note);
        self::assertCount(1, $individual->chan->note[0]->tran);

        $custom = $this->firstUnknown($individual->chan->note[0]->tran[0]->unknown, '_CUSTOM');

        self::assertInstanceOf(RawSubstructure::class, $custom);
        self::assertSame('x', $custom->value);
    }

    /**
     * A pointer-valued extension keeps its cross-reference target on `->xref` (and carries no line
     * value), so a preserved substructure that points rather than names is not flattened.
     */
    #[Test]
    public function aPointerValuedExtensionKeepsItsXref(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 NAME John /Doe/\n1 _LINK @X1@\n0 TRLR\n";

        $individual = $this->parseIndividual($gedcom);

        $link = $this->firstUnknown($individual->unknown, '_LINK');

        self::assertInstanceOf(RawSubstructure::class, $link);
        self::assertSame('X1', $link->xref);
        self::assertNull($link->value);
    }

    /**
     * A modelled tag still maps to its typed field and does NOT leak into `$unknown` — the net only
     * captures what the typed model did not consume.
     */
    #[Test]
    public function aModelledTagIsNotCapturedAsUnknown(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n0 TRLR\n";

        $individual = $this->parseIndividual($gedcom);

        self::assertSame('M', $individual->sex);
        self::assertNull($this->firstUnknown($individual->unknown, 'SEX'), 'A modelled tag must not appear in $unknown.');
        self::assertNull($this->firstUnknown($individual->unknown, 'NAME'), 'A modelled tag must not appear in $unknown.');
    }

    /**
     * Parses the given GEDCOM string and returns its single individual record.
     *
     * @param string $gedcom The GEDCOM source.
     *
     * @return IndividualRecord The parsed individual.
     */
    private function parseIndividual(string $gedcom): IndividualRecord
    {
        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        $document = (new Parser($stream))->parse();

        self::assertCount(1, $document->individuals);

        return $document->individuals[0];
    }

    /**
     * Returns the first preserved substructure with the given tag, or NULL when none is present.
     *
     * @param list<RawSubstructure> $unknown The preserved substructures.
     * @param string                $tag     The tag to look for.
     *
     * @return RawSubstructure|null The first match, or NULL.
     */
    private function firstUnknown(array $unknown, string $tag): ?RawSubstructure
    {
        foreach (array_values($unknown) as $raw) {
            if ($raw->tag === $tag) {
                return $raw;
            }
        }

        return null;
    }
}
