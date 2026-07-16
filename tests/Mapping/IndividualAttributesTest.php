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
use MagicSunday\Gedcom\Model\AttributeDetail;
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
 * The individual record now types the standard GEDCOM attribute tags (occupation, residence,
 * education, …) as {@see AttributeDetail} lists — carrying the attribute's value, its `TYPE`, and the
 * same date/place/age event detail — so a consumer navigates them typed rather than reaching for
 * `$unknown` (#132, additive roll-out).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(AttributeDetail::class)]
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
#[UsesClass(RawSubstructure::class)]
class IndividualAttributesTest extends TestCase
{
    /**
     * A value-bearing occupation is typed with its value, classifying TYPE and its event detail.
     */
    #[Test]
    public function typesAValueBearingOccupation(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OCCU Baker\n2 TYPE Trade\n2 DATE 1900\n2 PLAC Berlin\n0 TRLR\n"
        )->individuals[0];

        self::assertCount(1, $individual->occu);

        $occu = $individual->occu[0];
        self::assertSame('Baker', $occu->value);
        self::assertSame('Trade', $occu->type);
        self::assertSame('1900', $occu->date?->raw);
        self::assertSame('Berlin', $occu->plac?->raw);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A value-less residence (5.5.1 `RESI`) is typed with a NULL value and its place detail.
     */
    #[Test]
    public function typesAValuelessResidence(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 RESI\n2 PLAC Hamburg\n0 TRLR\n"
        )->individuals[0];

        self::assertCount(1, $individual->resi);
        self::assertNull($individual->resi[0]->value);
        self::assertSame('Hamburg', $individual->resi[0]->plac?->raw);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * Several distinct attributes are each typed onto their own property, including a count-valued
     * one (`NCHI`) and a title (`TITL`).
     */
    #[Test]
    public function typesDistinctAttributes(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 EDUC University\n1 NCHI 3\n1 TITL Baron\n1 RELI Catholic\n0 TRLR\n"
        )->individuals[0];

        self::assertSame('University', $individual->educ[0]->value);
        self::assertSame('3', $individual->nchi[0]->value);
        self::assertSame('Baron', $individual->titl[0]->value);
        self::assertSame('Catholic', $individual->reli[0]->value);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A tag beneath an attribute that AttributeDetail does not model (an extension) is still
     * preserved on the attribute's own `$unknown`, not lost.
     */
    #[Test]
    public function preservesAnUnmodelledTagUnderAnAttribute(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OCCU Baker\n2 _CUSTOM x\n0 TRLR\n"
        )->individuals[0];

        self::assertSame(['_CUSTOM'], $this->tags($individual->occu[0]->unknown));
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
