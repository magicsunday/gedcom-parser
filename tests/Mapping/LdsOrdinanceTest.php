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
use MagicSunday\Gedcom\Model\ExactDate;
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\Substructure\Common\LdsOrdinance;
use MagicSunday\Gedcom\Model\Substructure\Common\OrdinanceStatus;
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
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * The individual and family records now type their LDS ordinances — the individual rites (`BAPL`,
 * `CONL`, `ENDL`, `INIL`, `SLGC`) and the spouse sealing (`SLGS`) — as typed {@see LdsOrdinance}
 * objects rather than leaving them on `$unknown` (#132, additive roll-out). Each ordinance's date,
 * temple, place, completion status (a nested {@see OrdinanceStatus} carrying its own date), notes and
 * source citations are preserved, and a child-to-parents sealing additionally keeps its family
 * pointer.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(FamilyRecord::class)]
#[CoversClass(LdsOrdinance::class)]
#[CoversClass(OrdinanceStatus::class)]
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
#[UsesClass(ExactDate::class)]
#[UsesClass(PlaceValue::class)]
#[UsesClass(RawSubstructure::class)]
class LdsOrdinanceTest extends TestCase
{
    /**
     * An individual's baptism ordinance carries its date, temple, place, status (with the status's
     * own date), notes and source citations; an unmodelled child stays on the ordinance's `$unknown`.
     */
    #[Test]
    public function typesAnIndividualsBaptismOrdinance(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BAPL\n2 DATE 5 MAY 1990\n2 TEMP SLAKE\n2 PLAC Salt Lake City\n"
            . "2 STAT COMPLETED\n3 DATE 6 MAY 1990\n4 TIME 12:00:00\n2 NOTE a note\n2 SOUR a citation\n2 _CUSTOM extension\n"
            . "0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->bapl);
        $ordinance = $individual->bapl[0];
        self::assertSame('5 MAY 1990', $ordinance->date?->raw);
        self::assertSame('SLAKE', $ordinance->temp);
        self::assertSame('Salt Lake City', $ordinance->plac?->raw);
        self::assertNotNull($ordinance->stat);
        self::assertSame('COMPLETED', $ordinance->stat->value);
        $statusDate = $ordinance->stat->date;
        self::assertNotNull($statusDate);
        self::assertSame('6 MAY 1990', $statusDate->value);
        self::assertSame('12:00:00', $statusDate->time);
        self::assertCount(1, $ordinance->note);
        self::assertSame('a note', $ordinance->note[0]->value);
        self::assertCount(1, $ordinance->sour);
        self::assertSame('a citation', $ordinance->sour[0]->value);
        self::assertSame(['_CUSTOM'], $this->tags($ordinance->unknown));
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A child-to-parents sealing (SLGC) additionally keeps its family cross-reference pointer.
     */
    #[Test]
    public function typesAChildToParentsSealingWithItsFamilyPointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 SLGC\n2 FAMC @F1@\n2 DATE 1990\n2 SNOTE @N1@\n"
            . "0 @F1@ FAM\n0 @N1@ SNOTE A shared note\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->slgc);
        self::assertSame('F1', $individual->slgc[0]->famc);
        self::assertSame('1990', $individual->slgc[0]->date?->raw);
        self::assertSame(['N1'], $individual->slgc[0]->snote);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * The remaining individual ordinance tags (confirmation, endowment, initiatory) each map to an
     * LdsOrdinance too — guarding their per-tag type annotations against a silent divert to
     * `$unknown`.
     */
    #[Test]
    public function typesTheRemainingIndividualOrdinanceTags(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 CONL\n2 DATE 1991\n1 ENDL\n2 DATE 1992\n1 INIL\n2 DATE 1993\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertSame('1991', $individual->conl[0]->date?->raw);
        self::assertSame('1992', $individual->endl[0]->date?->raw);
        self::assertSame('1993', $individual->inil[0]->date?->raw);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A family's spouse-sealing ordinance is typed too.
     */
    #[Test]
    public function typesAFamilysSpouseSealing(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 SLGS\n2 DATE 1990\n2 STAT COMPLETED\n3 DATE 1991\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertCount(1, $family->slgs);
        self::assertSame('1990', $family->slgs[0]->date?->raw);
        $status = $family->slgs[0]->stat;
        self::assertNotNull($status);
        self::assertSame('COMPLETED', $status->value);
        self::assertSame('1991', $status->date?->value);
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
