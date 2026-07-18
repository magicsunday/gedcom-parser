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
use MagicSunday\Gedcom\Model\EventDetail;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SubmitterRecord;
use MagicSunday\Gedcom\Model\Substructure\Common\Address;
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
use function file_get_contents;

/**
 * Events, attributes and the contact-bearing records now type their structured address (`ADDR`) as a
 * typed {@see Address} rather than leaving it on the carrying object's `$unknown` (#132, #166, #168).
 *
 * The structure is identical in both GEDCOM versions: a free-form address as the line value, refined
 * by the optional street lines (`ADR1`–`ADR3`), city, state, postal code and country. The address was
 * the last untyped substructure of an individual's event and attribute detail; the family-event age
 * blocks ({@see FamilyEventAgeTest}) complete the set for family events.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Address::class)]
#[CoversClass(EventDetail::class)]
#[CoversClass(AttributeDetail::class)]
#[CoversClass(SubmitterRecord::class)]
#[CoversClass(RepositoryRecord::class)]
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
class StructuredAddressTest extends TestCase
{
    /**
     * An event's address is typed with its free-form value and every refining part.
     */
    #[Test]
    public function typesTheAddressOfAnEvent(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 ADDR 123 Main St\n3 ADR1 123 Main St\n3 ADR2 Apartment 4\n"
            . "3 ADR3 Rear building\n3 CITY Boston\n3 STAE Massachusetts\n3 POST 02101\n3 CTRY USA\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $address = $individual->birt[0]->addr;

        self::assertInstanceOf(Address::class, $address);
        self::assertSame('123 Main St', $address->value);
        self::assertSame('123 Main St', $address->adr1);
        self::assertSame('Apartment 4', $address->adr2);
        self::assertSame('Rear building', $address->adr3);
        self::assertSame('Boston', $address->city);
        self::assertSame('Massachusetts', $address->stae);
        self::assertSame('02101', $address->post);
        self::assertSame('USA', $address->ctry);
        self::assertSame([], $this->tags($individual->birt[0]->unknown));
    }

    /**
     * An attribute carries the same typed address, under GEDCOM 5.5.1 as well.
     */
    #[Test]
    public function typesTheAddressOfAnAttribute(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 RESI\n2 ADDR 5 Church Lane\n3 ADR1 Flat 2\n3 ADR2 Second floor\n"
            . "3 ADR3 Rear\n3 CITY Salem\n3 STAE Massachusetts\n3 POST 01970\n3 CTRY USA\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $address = $individual->resi[0]->addr;

        self::assertInstanceOf(Address::class, $address);
        self::assertSame('5 Church Lane', $address->value);
        self::assertSame('Flat 2', $address->adr1);
        self::assertSame('Second floor', $address->adr2);
        self::assertSame('Rear', $address->adr3);
        self::assertSame('Salem', $address->city);
        self::assertSame('Massachusetts', $address->stae);
        self::assertSame('01970', $address->post);
        self::assertSame('USA', $address->ctry);
        self::assertSame([], $this->tags($individual->resi[0]->unknown));
    }

    /**
     * The address is the one structure the specification expects to span several lines, so a
     * `CONT`-continued value is folded into the typed value rather than surfacing as a substructure.
     */
    #[Test]
    public function foldsAContinuedAddressIntoItsValue(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 ADDR Line one\n3 CONT Line two\n3 CONT Line three\n"
            . "3 CITY Boston\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $address = $individual->birt[0]->addr;

        self::assertInstanceOf(Address::class, $address);
        self::assertSame("Line one\nLine two\nLine three", $address->value);
        self::assertSame('Boston', $address->city);
        self::assertSame([], $this->tags($address->unknown), 'The continuation is folded, not preserved as a tag.');
    }

    /**
     * An address written with no line value at all still types, carrying only its parts.
     */
    #[Test]
    public function typesAnAddressWithoutALineValue(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 ADDR\n3 CITY Boston\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $address = $individual->birt[0]->addr;

        self::assertInstanceOf(Address::class, $address);
        self::assertNull($address->value);
        self::assertSame('Boston', $address->city);
    }

    /**
     * A carrier that models no address of its own keeps the tag verbatim rather than dropping it —
     * here an individual record, which the schema does not permit an address on either.
     */
    #[Test]
    public function preservesAnAddressOnACarrierThatModelsNone(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ADDR 123 Main St\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertSame(['ADDR'], $this->tags($individual->unknown));
        self::assertSame('123 Main St', $individual->unknown[0]->value);
    }

    /**
     * The submitter and repository records type the address of their contact block too.
     */
    #[Test]
    public function typesTheAddressOfTheContactRecords(): void
    {
        $document = $this->parse(
            "0 @U1@ SUBM\n1 NAME A submitter\n1 ADDR 1 Archive Way\n2 CITY Boston\n"
            . "0 @R1@ REPO\n1 NAME A repository\n1 ADDR 2 Library Road\n2 POST 02102\n0 TRLR\n",
            '7.0'
        );

        $submitterAddress  = $document->submitters[0]->addr;
        $repositoryAddress = $document->repositories[0]->addr;

        self::assertInstanceOf(Address::class, $submitterAddress);
        self::assertSame('1 Archive Way', $submitterAddress->value);
        self::assertSame('Boston', $submitterAddress->city);

        self::assertInstanceOf(Address::class, $repositoryAddress);
        self::assertSame('2 Library Road', $repositoryAddress->value);
        self::assertSame('02102', $repositoryAddress->post);

        self::assertSame([], $this->tags($document->submitters[0]->unknown));
        self::assertSame([], $this->tags($document->repositories[0]->unknown));
    }

    /**
     * An address given as a bare line, without any refining part, still types.
     */
    #[Test]
    public function typesABareAddressLine(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 ADDR 123 Main St\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $address = $individual->birt[0]->addr;

        self::assertInstanceOf(Address::class, $address);
        self::assertSame('123 Main St', $address->value);
        self::assertNull($address->city);
        self::assertSame([], $this->tags($individual->birt[0]->unknown));
    }

    /**
     * An out-of-schema substructure beneath the address is preserved verbatim rather than dropped.
     */
    #[Test]
    public function preservesAnUnknownSubstructureBeneathTheAddress(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 ADDR 123 Main St\n3 _CUSTOM Extension payload\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $address = $individual->birt[0]->addr;

        self::assertInstanceOf(Address::class, $address);
        self::assertSame('123 Main St', $address->value);
        self::assertSame(['_CUSTOM'], $this->tags($address->unknown));
        self::assertSame('Extension payload', $address->unknown[0]->value);
    }

    /**
     * The address types out of the shipped conformance corpus, whose submitter carries the shape
     * real files use: several consecutive continuation lines alongside the refining parts.
     */
    #[Test]
    public function typesTheAddressOfTheConformanceCorpus(): void
    {
        $path = __DIR__ . '/../files/allged.ged';
        self::assertFileExists($path);

        $stream = (new StreamFactory())->createStream((string) file_get_contents($path));
        $stream->rewind();

        $address = (new Parser($stream))->parse()->submitters[0]->addr;

        self::assertInstanceOf(Address::class, $address);
        self::assertNotNull($address->value);
        self::assertStringContainsString("\n", $address->value, 'The corpus address spans several lines.');
        self::assertNotNull($address->adr1);
        self::assertNotNull($address->city);
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
    private function parse(string $body, string $version): GedcomDocument
    {
        $charset = $version === '5.5.1' ? "1 CHAR ASCII\n" : '';
        $gedcom  = "0 HEAD\n1 GEDC\n2 VERS " . $version . "\n" . $charset . $body;

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
