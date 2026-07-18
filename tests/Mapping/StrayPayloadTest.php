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
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitationData;
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
 * A line value written beside a structure the schema declares payload-less is preserved, and costs
 * nothing else (#180).
 *
 * Offering such a value to a model that has nowhere to put it did not merely lose the value: the
 * rejected key took the whole object with it, so one stray word beside a container discarded every
 * well-formed substructure below it. The value is now kept out of the shape and carried verbatim
 * under the structure's own tag instead.
 *
 * A value object is the deliberate exception — its consumed tags name its child tags, never its own
 * payload, which its grammar is precisely what reads.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomObjectMapper::class)]
#[UsesClass(Parser::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(Reader::class)]
#[UsesClass(GedcomDocumentReader::class)]
#[UsesClass(GedcomVersionDetector::class)]
#[UsesClass(RecordStream::class)]
#[UsesClass(GedcomTreeReader::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(JsonMapperFactory::class)]
#[UsesClass(RegistrySchemaLoader::class)]
#[UsesClass(Schema::class)]
#[UsesClass(GedcomVersion::class)]
#[UsesClass(GedcomDocument::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(SourceRecord::class)]
#[UsesClass(SourceCitation::class)]
#[UsesClass(SourceCitationData::class)]
#[UsesClass(DateValue::class)]
#[UsesClass(PlaceValue::class)]
#[UsesClass(RawSubstructure::class)]
class StrayPayloadTest extends TestCase
{
    /**
     * A stray value beside a payload-less container is preserved, and every substructure below it
     * still types.
     */
    #[Test]
    public function preservesAStrayPayloadWithoutCostingTheContainer(): void
    {
        $data = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 DATA legacy payload\n4 DATE 1 JAN 1900\n"
            . "4 TEXT A transcribed line\n0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->sour[0]->data;

        self::assertInstanceOf(SourceCitationData::class, $data);
        self::assertSame('1 JAN 1900', $data->date?->raw, 'The block survives the stray value.');
        self::assertSame(['A transcribed line'], $data->text);

        self::assertSame(['DATA'], $this->tags($data->unknown));
        self::assertSame('legacy payload', $data->unknown[0]->value);
    }

    /**
     * A line value is either text or a pointer, and both are withheld on the same terms — a stray
     * pointer beside a payload-less container cost the whole block just as a stray word did.
     */
    #[Test]
    public function preservesAStrayPointerWithoutCostingTheContainer(): void
    {
        $data = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 DATA @S1@\n4 DATE 1 JAN 1900\n"
            . "4 TEXT A transcribed line\n0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->sour[0]->data;

        self::assertInstanceOf(SourceCitationData::class, $data);
        self::assertSame('1 JAN 1900', $data->date?->raw, 'The block survives the stray pointer.');
        self::assertSame(['A transcribed line'], $data->text);

        self::assertSame(['DATA'], $this->tags($data->unknown));
        self::assertSame('S1', $data->unknown[0]->xref);
        self::assertNull($data->unknown[0]->value);
    }

    /**
     * The preserved payload keeps its place: it is written on the container's own line, ahead of the
     * children, and is preserved ahead of them too.
     */
    #[Test]
    public function preservesTheStrayPayloadInDocumentOrder(): void
    {
        $data = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 DATA legacy payload\n4 DATA nested\n"
            . "4 DATE 1 JAN 1900\n0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->sour[0]->data;

        self::assertInstanceOf(SourceCitationData::class, $data);
        self::assertSame('1 JAN 1900', $data->date?->raw);
        self::assertSame(['DATA', 'DATA'], $this->tags($data->unknown));
        self::assertSame('legacy payload', $data->unknown[0]->value, "The container's own payload comes first.");
        self::assertSame('nested', $data->unknown[1]->value);
    }

    /**
     * The container maps unchanged when no stray value is written beside it.
     */
    #[Test]
    public function leavesAWellFormedContainerUntouched(): void
    {
        $data = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 DATA\n4 DATE 1 JAN 1900\n"
            . "4 TEXT A transcribed line\n0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->sour[0]->data;

        self::assertInstanceOf(SourceCitationData::class, $data);
        self::assertSame('1 JAN 1900', $data->date?->raw);
        self::assertSame([], $this->tags($data->unknown));
    }

    /**
     * A value object's own payload is never treated as stray — its grammar is what reads it, and its
     * consumed tags name only its children. Were it diverted, every date, place and age in the
     * library would come back empty.
     */
    #[Test]
    public function neverTreatsAValueObjectPayloadAsStray(): void
    {
        $event = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 DATE 1 JAN 1900\n3 PHRASE around new year\n"
            . "2 PLAC Boston, Suffolk\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0];

        $date = $event->date;
        self::assertInstanceOf(DateValue::class, $date);
        self::assertSame('1 JAN 1900', $date->raw, 'The date keeps its own payload.');
        self::assertSame('around new year', $date->phrase);

        $place = $event->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(['Boston', 'Suffolk'], $place->levels, 'The place keeps its own payload.');
    }

    /**
     * The same holds under GEDCOM 5.5.1.
     */
    #[Test]
    public function preservesAStrayPayloadUnderGedcom551(): void
    {
        $data = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 DATA legacy payload\n4 TEXT A transcribed line\n"
            . "0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n",
            '5.5.1'
        )->individuals[0]->birt[0]->sour[0]->data;

        self::assertInstanceOf(SourceCitationData::class, $data);
        self::assertSame(['A transcribed line'], $data->text);
        self::assertSame(['DATA'], $this->tags($data->unknown));
        self::assertSame('legacy payload', $data->unknown[0]->value);
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
