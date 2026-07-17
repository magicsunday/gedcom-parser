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
 * Events and attributes now type their shared `EVENT_DETAIL` contact block — phone numbers (`PHON`),
 * email addresses (`EMAIL`), fax numbers (`FAX`) and web pages (`WWW`) — as repeating string lists
 * rather than leaving them on the detail's `$unknown` (#132, #166). The block is a direct event-detail
 * child in both GEDCOM versions (GEDCOM 5.5.1 caps each at three, GEDCOM 7.0 does not).
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
class EventAttributeContactTest extends TestCase
{
    /**
     * An event types its contact block; a repeated tag keeps every occurrence.
     */
    #[Test]
    public function typesTheEventContactBlock(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 DEAT\n2 PHON +1 555 0001\n2 PHON +1 555 0002\n2 EMAIL home@example.test\n"
            . "2 FAX +1 555 0003\n2 WWW https://example.test\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $event = $individual->deat[0];
        self::assertSame(['+1 555 0001', '+1 555 0002'], $event->phon);
        self::assertSame(['home@example.test'], $event->email);
        self::assertSame(['+1 555 0003'], $event->fax);
        self::assertSame(['https://example.test'], $event->www);
        self::assertSame([], $this->tags($event->unknown));
    }

    /**
     * A GEDCOM 5.5.1 attribute types its contact block too.
     */
    #[Test]
    public function typesA551AttributeContactBlock(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OCCU Farmer\n2 PHON +1 555 0100\n2 EMAIL work@example.test\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $attribute = $individual->occu[0];
        self::assertSame('Farmer', $attribute->value);
        self::assertSame(['+1 555 0100'], $attribute->phon);
        self::assertSame(['work@example.test'], $attribute->email);
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
