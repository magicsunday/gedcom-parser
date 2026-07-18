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
use MagicSunday\Gedcom\Model\Substructure\Common\AdoptingParent;
use MagicSunday\Gedcom\Model\Substructure\Common\EventFamilyChild;
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
 * The birth, christening and adoption events now type their family-child pointer (`FAMC`) — the
 * family the child belongs to — as a typed {@see EventFamilyChild} rather than leaving it on the
 * event detail's `$unknown` (#132, #166).
 *
 * The pointer is version-agnostic: GEDCOM 5.5.1 and GEDCOM 7.0 both permit it on `BIRT` and `CHR`
 * bare, and on `ADOP` qualified by the adopting parent (`HUSB`, `WIFE` or `BOTH`) — itself typed as
 * an {@see AdoptingParent}, since GEDCOM 7.0 lets a free-text phrase qualify that value.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(EventFamilyChild::class)]
#[CoversClass(AdoptingParent::class)]
#[CoversClass(EventDetail::class)]
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
#[UsesClass(IndividualRecord::class)]
#[UsesClass(RawSubstructure::class)]
class EventFamilyChildTest extends TestCase
{
    /**
     * A birth event types its bare family-child pointer, leaving the adopting parent unset.
     */
    #[Test]
    public function typesTheBirthFamilyChildPointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 DATE 1900\n2 FAMC @F1@\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $famc = $individual->birt[0]->famc;

        self::assertInstanceOf(EventFamilyChild::class, $famc);
        self::assertSame('F1', $famc->xref);
        self::assertNull($famc->adop);
        self::assertNull($famc->value, 'A pointer payload does not populate the text value.');
        self::assertSame([], $this->tags($individual->birt[0]->unknown));
    }

    /**
     * A file that misuses the pointer to carry free text keeps that non-conformant payload on the
     * typed model rather than dropping it.
     */
    #[Test]
    public function toleratesANonPointerFamilyChildValue(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 FAMC Family of record\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $famc = $individual->birt[0]->famc;

        self::assertInstanceOf(EventFamilyChild::class, $famc);
        self::assertNull($famc->xref);
        self::assertSame('Family of record', $famc->value);
        self::assertSame([], $this->tags($individual->birt[0]->unknown));
    }

    /**
     * A christening event types the same bare pointer.
     */
    #[Test]
    public function typesTheChristeningFamilyChildPointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 CHR\n2 FAMC @F2@\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertSame('F2', $individual->chr[0]->famc?->xref);
        self::assertSame([], $this->tags($individual->chr[0]->unknown));
    }

    /**
     * An adoption event types the pointer together with the parent that adopted the child.
     */
    #[Test]
    public function typesTheAdoptionFamilyChildWithAdoptingParent(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ADOP\n2 FAMC @F3@\n3 ADOP BOTH\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $famc = $individual->adop[0]->famc;

        self::assertInstanceOf(EventFamilyChild::class, $famc);
        self::assertSame('F3', $famc->xref);
        self::assertSame('BOTH', $famc->adop?->value);
        self::assertSame([], $this->tags($famc->unknown));
    }

    /**
     * The GEDCOM 7.0 free-text phrase qualifying the adopting parent is typed alongside its value.
     */
    #[Test]
    public function typesTheAdoptingParentPhrase(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ADOP\n2 FAMC @F3@\n3 ADOP BOTH\n4 PHRASE Adopted jointly\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $adopting = $individual->adop[0]->famc?->adop;

        self::assertInstanceOf(AdoptingParent::class, $adopting);
        self::assertSame('BOTH', $adopting->value);
        self::assertSame('Adopted jointly', $adopting->phrase);
    }

    /**
     * An adopting parent outside the enumerated set is kept verbatim rather than rejected, so an
     * extension survives.
     */
    #[Test]
    public function toleratesAnUnlistedAdoptingParent(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ADOP\n2 FAMC @F6@\n3 ADOP _STEPMOTHER\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertSame('_STEPMOTHER', $individual->adop[0]->famc?->adop?->value);
    }

    /**
     * Every form of the pointer types under GEDCOM 5.5.1 too — bare on the birth and christening,
     * and qualified by the adopting parent on the adoption.
     */
    #[Test]
    public function typesEveryFamilyChildPointerUnderGedcom551(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 FAMC @F1@\n1 CHR\n2 FAMC @F2@\n1 ADOP\n2 FAMC @F4@\n3 ADOP HUSB\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $adoption = $individual->adop[0]->famc;

        self::assertSame('F1', $individual->birt[0]->famc?->xref);
        self::assertSame('F2', $individual->chr[0]->famc?->xref);
        self::assertInstanceOf(EventFamilyChild::class, $adoption);
        self::assertSame('F4', $adoption->xref);
        self::assertSame('HUSB', $adoption->adop?->value);
    }

    /**
     * An out-of-schema substructure beneath the pointer is preserved verbatim rather than dropped.
     */
    #[Test]
    public function preservesAnUnknownSubstructureBeneathThePointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ADOP\n2 FAMC @F5@\n3 _CUSTOM Extension payload\n3 ADOP BOTH\n4 _NESTED Nested payload\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $famc = $individual->adop[0]->famc;

        self::assertInstanceOf(EventFamilyChild::class, $famc);
        self::assertSame('F5', $famc->xref);
        self::assertSame(['_CUSTOM'], $this->tags($famc->unknown));
        self::assertSame('Extension payload', $famc->unknown[0]->value);
        self::assertInstanceOf(AdoptingParent::class, $famc->adop);
        self::assertSame(['_NESTED'], $this->tags($famc->adop->unknown));
    }

    /**
     * An event the schema does not permit a family-child pointer on keeps that pointer verbatim on
     * the event's own `$unknown` rather than typing it — the property is shared by every event, but
     * the pointer is not.
     */
    #[Test]
    public function doesNotTypeTheFamilyChildPointerOnAnUnpermittedEvent(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 DEAT\n2 FAMC @F7@\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertNull($individual->deat[0]->famc);
        self::assertSame(['FAMC'], $this->tags($individual->deat[0]->unknown));
        self::assertSame('F7', $individual->deat[0]->unknown[0]->xref);
    }

    /**
     * The adopting-parent qualifier is scoped to the adoption event: beneath a birth's pointer the
     * schema does not permit it, so it is preserved verbatim rather than consumed.
     */
    #[Test]
    public function doesNotConsumeAnAdoptingParentBeneathABirthPointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 FAMC @F8@\n3 ADOP BOTH\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $famc = $individual->birt[0]->famc;

        self::assertInstanceOf(EventFamilyChild::class, $famc);
        self::assertSame('F8', $famc->xref);
        self::assertNull($famc->adop);
        self::assertSame(['ADOP'], $this->tags($famc->unknown));
        self::assertSame('BOTH', $famc->unknown[0]->value);
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
