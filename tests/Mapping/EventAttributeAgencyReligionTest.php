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
 * Events and attributes now type their shared `EVENT_DETAIL` responsible-agency (`AGNC`) and
 * religious-affiliation (`RELI`) substructures rather than leaving them on the detail's `$unknown`
 * (#132, #166). Both are direct event-detail children in both GEDCOM versions.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(EventDetail::class)]
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
#[UsesClass(IndividualRecord::class)]
#[UsesClass(RawSubstructure::class)]
class EventAttributeAgencyReligionTest extends TestCase
{
    /**
     * An event types its responsible agency and religious affiliation.
     */
    #[Test]
    public function typesTheEventAgencyAndReligion(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BAPM\n2 AGNC A parish\n2 RELI Catholic\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $event = $individual->bapm[0];
        self::assertSame('A parish', $event->agnc);
        self::assertSame('Catholic', $event->reli);
        self::assertSame([], $this->tags($event->unknown));
    }

    /**
     * A GEDCOM 5.5.1 attribute types the same substructures alongside its value.
     */
    #[Test]
    public function typesA551AttributeAgencyAndReligion(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OCCU Farmer\n2 AGNC A guild\n2 RELI Methodist\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $attribute = $individual->occu[0];
        self::assertSame('Farmer', $attribute->value);
        self::assertSame('A guild', $attribute->agnc);
        self::assertSame('Methodist', $attribute->reli);
        self::assertSame([], $this->tags($attribute->unknown));
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
