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
 * A record now types its own record-level notes (`NOTE`) — the cross-cutting annotation every record
 * carries — as a typed {@see Note} list, reusing the same class the substructures already use, so a
 * consumer navigates a record's notes typed rather than reaching for `$unknown` (#132, additive
 * roll-out).
 *
 * The record-level source citation (`SOUR`) is deliberately NOT modelled here: its 5.5.1 inline
 * variant (SOUR-SOURCE_DESCRIPTION) carries a free-text description as its line value, and the
 * reused {@see \MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation} has no field for that
 * value, so consuming it would silently drop the description that is currently preserved verbatim on
 * `$unknown`. It stays on `$unknown` pending a
 * lossless model (tracked separately). The GEDCOM 7.0 shared-note pointer (`SNOTE`) is likewise a
 * distinct, unmodelled tag preserved on `$unknown`.
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
#[UsesClass(RawSubstructure::class)]
class RecordNotesAndSourcesTest extends TestCase
{
    /**
     * An individual's inline note and pointer note are both typed, while an unmodelled tag (a custom
     * extension) and the deferred source citation are preserved on `$unknown`.
     */
    #[Test]
    public function typesAnIndividualsNotesAndDefersTheSourceCitation(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 NOTE A personal note\n1 NOTE @N1@\n1 SOUR A free-text citation\n1 _CUSTOM x\n"
            . "0 @N1@ NOTE A shared note\n0 TRLR\n"
        )->individuals[0];

        self::assertCount(2, $individual->note);
        self::assertSame('A personal note', $individual->note[0]->value);
        self::assertSame('N1', $individual->note[1]->value);

        // SOUR stays on $unknown (its inline description text would be lost by SourceCitation); the
        // custom tag is the positive control proving $unknown is actually populated, not merely empty.
        self::assertSame(['SOUR', '_CUSTOM'], $this->tags($individual->unknown));
    }

    /**
     * A family's record-level note is typed too.
     */
    #[Test]
    public function typesAFamilysNote(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 NOTE A family note\n0 TRLR\n"
        )->families[0];

        self::assertCount(1, $family->note);
        self::assertSame('A family note', $family->note[0]->value);
        self::assertSame([], $this->tags($family->unknown));
    }

    /**
     * Under GEDCOM 7.0 the record-level note types identically (carrying its MIME/LANG leaves), while
     * the shared-note pointer (SNOTE) — a distinct tag this batch does not model — is preserved on
     * `$unknown` rather than silently lost or mis-mapped.
     */
    #[Test]
    public function typesTheNoteAndPreservesSharedNoteUnderGedcom70(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 NOTE A personal note\n2 MIME text/plain\n2 LANG en\n1 SNOTE @N1@\n"
            . "0 @N1@ SNOTE A shared note\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->note);
        self::assertSame('A personal note', $individual->note[0]->value);
        self::assertSame('text/plain', $individual->note[0]->mime);
        self::assertSame('en', $individual->note[0]->lang);

        self::assertSame(['SNOTE'], $this->tags($individual->unknown));
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
