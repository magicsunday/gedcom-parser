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
use MagicSunday\Gedcom\Model\Substructure\Common\MultimediaLink;
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
 * Events and attributes now type their shared `EVENT_DETAIL` multimedia links (`OBJE`) as typed
 * {@see MultimediaLink} objects rather than leaving them on the detail's `$unknown` (#132, #163). The
 * pointer form keeps its multimedia-record cross-reference and its optional title, and a GEDCOM 5.5.1
 * inline object preserves its embedded block on the link's own `$unknown`.
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
#[UsesClass(MultimediaLink::class)]
#[UsesClass(RawSubstructure::class)]
class EventAttributeMultimediaTest extends TestCase
{
    /**
     * A GEDCOM 7.0 event types its multimedia link (pointer plus overriding title).
     */
    #[Test]
    public function typesTheEventMultimediaLink(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 DEAT\n2 OBJE @M1@\n3 TITL A photo\n0 @M1@ OBJE\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $event = $individual->deat[0];
        self::assertCount(1, $event->obje);
        self::assertSame('M1', $event->obje[0]->xref);
        self::assertSame('A photo', $event->obje[0]->titl);
        self::assertSame([], $this->tags($event->unknown));
    }

    /**
     * A GEDCOM 5.5.1 attribute types its inline multimedia object; the embedded FILE block stays on
     * the link's own `$unknown`.
     */
    #[Test]
    public function typesA551AttributeInlineMultimedia(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OCCU Farmer\n1 RESI\n2 OBJE\n3 FILE photo.jpg\n4 FORM jpeg\n3 TITL Old photo\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $attribute = $individual->resi[0];
        self::assertCount(1, $attribute->obje);
        self::assertNull($attribute->obje[0]->xref);
        self::assertSame('Old photo', $attribute->obje[0]->titl);

        // The embedded FILE block is preserved verbatim on the link's own $unknown — its value and its
        // nested FORM child, not merely the tag.
        $file = $attribute->obje[0]->unknown[0];
        self::assertSame('FILE', $file->tag);
        self::assertSame('photo.jpg', $file->value);
        self::assertSame(['FORM'], $this->tags($file->children));
        self::assertSame('jpeg', $file->children[0]->value);
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
