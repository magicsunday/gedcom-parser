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
 * The individual record now types the remaining standard GEDCOM life-event tags (baptism,
 * christening, cremation, emigration, …) as {@see EventDetail} lists — the same shape as birth and
 * death — so a consumer navigates them typed rather than reaching for `$unknown` (#132, additive
 * roll-out).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
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
#[UsesClass(EventDetail::class)]
#[UsesClass(RawSubstructure::class)]
class IndividualEventsTest extends TestCase
{
    /**
     * A baptism event is typed as an EventDetail, carrying its date and place, and is no longer
     * left on `$unknown`.
     */
    #[Test]
    public function typesABaptismEvent(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BAPM\n2 DATE 2 FEB 1900\n2 PLAC Berlin\n0 TRLR\n"
        )->individuals[0];

        self::assertCount(1, $individual->bapm);
        self::assertSame('2 FEB 1900', $individual->bapm[0]->date?->raw);
        self::assertSame('Berlin', $individual->bapm[0]->plac?->raw);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * Several distinct life events are each typed onto their own property.
     */
    #[Test]
    public function typesTheStandardLifeEvents(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 CHR\n2 DATE 3 MAR 1900\n1 CREM\n2 PLAC Hamburg\n1 EMIG\n2 DATE 1920\n0 TRLR\n"
        )->individuals[0];

        self::assertSame('3 MAR 1900', $individual->chr[0]->date?->raw);
        self::assertSame('Hamburg', $individual->crem[0]->plac?->raw);
        self::assertSame('1920', $individual->emig[0]->date?->raw);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * The adoption and census life events are typed too.
     */
    #[Test]
    public function typesAdoptionAndCensus(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ADOP\n2 DATE 1905\n1 CENS\n2 DATE 1910\n2 PLAC Munich\n0 TRLR\n"
        )->individuals[0];

        self::assertSame('1905', $individual->adop[0]->date?->raw);
        self::assertSame('1910', $individual->cens[0]->date?->raw);
        self::assertSame('Munich', $individual->cens[0]->plac?->raw);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A tag not modelled even after this batch (an attribute such as OCCU) is still preserved on
     * `$unknown`, unchanged.
     */
    #[Test]
    public function stillPreservesAStillUnmodelledTag(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OCCU Baker\n0 TRLR\n"
        )->individuals[0];

        self::assertSame(['OCCU'], $this->tags($individual->unknown));
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
     * Parses the given individual body into the first individual record.
     *
     * @param string $body The GEDCOM records.
     *
     * @return GedcomDocument The parsed document.
     */
    private function parse(string $body): GedcomDocument
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n" . $body;

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
