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
use MagicSunday\Gedcom\Model\Substructure\Common\Association;
use MagicSunday\Gedcom\Model\Substructure\Common\Role;
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
 * Events and attributes now type their GEDCOM 7.0-only shared `EVENT_DETAIL` substructures — the
 * associations (`ASSO`), the sort date (`SDATE`), the shared-note pointers (`SNOTE`) and the unique
 * identifiers (`UID`) — rather than leaving them on the detail's `$unknown` (#132, #166). Each stays
 * empty/NULL for a GEDCOM 5.5.1 record, which cannot carry them.
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
#[UsesClass(Association::class)]
#[UsesClass(Role::class)]
#[UsesClass(DateValue::class)]
#[UsesClass(RawSubstructure::class)]
class EventAttributeDetailV7Test extends TestCase
{
    /**
     * A GEDCOM 7.0 event types its association, sort date, shared-note pointer and unique identifier.
     */
    #[Test]
    public function typesTheEventV7Substructures(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 DEAT\n2 ASSO @I2@\n3 ROLE WITN\n2 SDATE 2000\n2 SNOTE @N1@\n2 UID abc-123\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $event = $individual->deat[0];
        self::assertCount(1, $event->asso);
        self::assertSame('I2', $event->asso[0]->xref);
        self::assertSame('WITN', $event->asso[0]->role?->value);
        self::assertSame('2000', $event->sdate?->raw);
        self::assertSame(['N1'], $event->snote);
        self::assertSame(['abc-123'], $event->uid);
        self::assertSame([], $this->tags($event->unknown));
    }

    /**
     * A GEDCOM 7.0 attribute types the same substructures alongside its value.
     */
    #[Test]
    public function typesTheAttributeV7Substructures(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OCCU Farmer\n2 ASSO @I2@\n3 ROLE OTHER\n2 SDATE 1990\n2 SNOTE @N1@\n2 UID xyz-9\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $attribute = $individual->occu[0];
        self::assertSame('Farmer', $attribute->value);
        self::assertSame('I2', $attribute->asso[0]->xref);
        self::assertSame('OTHER', $attribute->asso[0]->role?->value);
        self::assertSame('1990', $attribute->sdate?->raw);
        self::assertSame(['N1'], $attribute->snote);
        self::assertSame(['xyz-9'], $attribute->uid);
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
