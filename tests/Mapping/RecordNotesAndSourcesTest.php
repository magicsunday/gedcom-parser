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
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\SourceRecord;
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
 * A record now types its own record-level notes (`NOTE`) and source citations (`SOUR`) — the
 * cross-cutting annotations every record carries — as typed {@see Note} and {@see SourceCitation}
 * lists, reusing the same classes the substructures already use, so a consumer navigates a record's
 * notes and sources typed rather than reaching for `$unknown` (#132, additive roll-out).
 *
 * Both the pointer and the GEDCOM 5.5.1 inline variants are lossless: a pointer citation resolves to
 * its {@see SourceRecord}, and an inline citation retains its free-text description on
 * {@see SourceCitation::$value}. A tag the record does not model (a `_CUSTOM` extension) is
 * preserved on `$unknown`.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(FamilyRecord::class)]
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
#[UsesClass(SourceRecord::class)]
#[UsesClass(RawSubstructure::class)]
class RecordNotesAndSourcesTest extends TestCase
{
    /**
     * An individual's inline and pointer notes and source citations are all typed: the pointer
     * citation resolves to its source record and the inline citation keeps its description, while an
     * unmodelled custom tag is preserved on `$unknown`.
     */
    #[Test]
    public function typesAnIndividualsNotesAndSources(): void
    {
        $document = $this->parse(
            "0 @I1@ INDI\n1 NOTE A personal note\n1 NOTE @N1@\n"
            . "1 SOUR A free-text citation\n1 SOUR @S1@\n2 PAGE p. 5\n1 _CUSTOM x\n"
            . "0 @N1@ NOTE A shared note\n0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n"
        );

        $individual = $document->individuals[0];

        self::assertCount(2, $individual->note);
        self::assertSame('A personal note', $individual->note[0]->value);
        self::assertSame('N1', $individual->note[1]->value);

        self::assertCount(2, $individual->sour);
        // The inline citation retains its free-text description and carries no pointer.
        self::assertSame('A free-text citation', $individual->sour[0]->value);
        self::assertNull($individual->sour[0]->xref);
        // The pointer citation carries no inline value and resolves to its source record.
        self::assertNull($individual->sour[1]->value);
        self::assertSame('p. 5', $individual->sour[1]->page);
        self::assertSame('S1', $individual->sour[1]->source($document)?->xref);

        // Only the custom tag remains unmodelled — the positive control proving $unknown is populated.
        self::assertSame(['_CUSTOM'], $this->tags($individual->unknown));
    }

    /**
     * An inline source citation retains its description and preserves an unmodelled inline
     * substructure (TEXT) on the citation's own `$unknown`, so no inline payload is silently lost
     * alongside the retained value.
     */
    #[Test]
    public function anInlineSourceCitationPreservesUnmodelledSubstructuresOnItsOwnUnknown(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 SOUR A free-text citation\n2 TEXT a transcript line\n0 TRLR\n"
        )->individuals[0];

        self::assertSame('A free-text citation', $individual->sour[0]->value);
        self::assertSame(['TEXT'], $this->tags($individual->sour[0]->unknown));
    }

    /**
     * A family's record-level note and source citation are typed too.
     */
    #[Test]
    public function typesAFamilysNoteAndSource(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 NOTE A family note\n1 SOUR A free-text citation\n0 TRLR\n"
        )->families[0];

        self::assertCount(1, $family->note);
        self::assertSame('A family note', $family->note[0]->value);

        self::assertCount(1, $family->sour);
        self::assertSame('A free-text citation', $family->sour[0]->value);

        self::assertSame([], $this->tags($family->unknown));
    }

    /**
     * Under GEDCOM 7.0 the record-level note and (pointer-only) source citation type identically,
     * while an unmodelled tag (a `_CUSTOM` extension) is preserved on `$unknown` rather than silently
     * lost or mis-mapped.
     */
    #[Test]
    public function typesTheNoteAndSourceUnderGedcom70(): void
    {
        $document = $this->parse(
            "0 @I1@ INDI\n1 NOTE A personal note\n2 MIME text/plain\n2 LANG en\n"
            . "1 SOUR @S1@\n2 PAGE p. 7\n1 _CUSTOM person-1\n"
            . "0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n",
            '7.0'
        );

        $individual = $document->individuals[0];

        self::assertCount(1, $individual->note);
        self::assertSame('A personal note', $individual->note[0]->value);
        self::assertSame('text/plain', $individual->note[0]->mime);
        self::assertSame('en', $individual->note[0]->lang);

        self::assertCount(1, $individual->sour);
        self::assertSame('p. 7', $individual->sour[0]->page);
        self::assertSame('S1', $individual->sour[0]->source($document)?->xref);

        self::assertSame(['_CUSTOM'], $this->tags($individual->unknown));
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
