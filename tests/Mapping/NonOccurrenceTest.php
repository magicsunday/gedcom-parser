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
use MagicSunday\Gedcom\Model\Substructure\Common\NonOccurrence;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * The individual and family records now type their GEDCOM 7.0 non-occurrences (`NO`) — an assertion
 * that a kind of event did not happen — as typed {@see NonOccurrence} objects rather than leaving
 * them on `$unknown` (#132, additive roll-out). The assertion's event type, its date period and its
 * notes and source citations are all preserved.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(FamilyRecord::class)]
#[CoversClass(NonOccurrence::class)]
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
#[UsesClass(DateValue::class)]
#[UsesClass(RawSubstructure::class)]
class NonOccurrenceTest extends TestCase
{
    /**
     * An individual's non-occurrence carries its event type, date period, notes and source
     * citations; an unmodelled child is preserved on the non-occurrence's own `$unknown`.
     */
    #[Test]
    public function typesAnIndividualsNonOccurrence(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 NO NATU\n2 DATE FROM 1900 TO 1950\n2 NOTE never naturalised\n"
            . "2 SOUR a citation\n2 _CUSTOM extension\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->no);
        $nonOccurrence = $individual->no[0];
        self::assertSame('NATU', $nonOccurrence->value);
        self::assertSame('FROM 1900 TO 1950', $nonOccurrence->date?->raw);
        self::assertCount(1, $nonOccurrence->note);
        self::assertSame('never naturalised', $nonOccurrence->note[0]->value);
        self::assertCount(1, $nonOccurrence->sour);
        self::assertSame('a citation', $nonOccurrence->sour[0]->value);

        // The extension is preserved on the non-occurrence's own $unknown, not leaked to the record.
        self::assertSame(['_CUSTOM'], $this->tags($nonOccurrence->unknown));
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A family's non-occurrence is typed too.
     */
    #[Test]
    public function typesAFamilysNonOccurrence(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 NO ANUL\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertCount(1, $family->no);
        self::assertSame('ANUL', $family->no[0]->value);
        self::assertNull($family->no[0]->date);
        self::assertSame([], $this->tags($family->unknown));
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
