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
use MagicSunday\Gedcom\Model\Note;
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
 * Events and attributes now type more of their shared `EVENT_DETAIL` substructures — the
 * classification (`TYPE`, events only), the cause (`CAUS`), the restriction notice (`RESN`) and the
 * notes (`NOTE`) — rather than leaving them on the detail's `$unknown` (#132, #166). The structured
 * address (`ADDR`) is typed too, which completes the substructure set for an individual's events and
 * attributes; a family event's `HUSB`/`WIFE` age blocks remain a follow-up.
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
#[UsesClass(Note::class)]
#[UsesClass(RawSubstructure::class)]
class EventAttributeDetailExtrasTest extends TestCase
{
    /**
     * A death event types its classification, cause, restriction notice and notes; an extension the
     * schema does not recognise stays on the event's own `$unknown`.
     */
    #[Test]
    public function typesTheEventDescriptorSubstructures(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 DEAT\n2 TYPE Natural\n2 CAUS Heart failure\n2 RESN confidential\n"
            . "2 NOTE a death note\n2 _CUSTOM Extension payload\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $event = $individual->deat[0];
        self::assertSame('Natural', $event->type);
        self::assertSame('Heart failure', $event->caus);
        self::assertSame('confidential', $event->resn);
        self::assertCount(1, $event->note);
        self::assertSame('a death note', $event->note[0]->value);
        self::assertSame(['_CUSTOM'], $this->tags($event->unknown));
    }

    /**
     * The same descriptor substructures type under GEDCOM 5.5.1, where they are equally part of the
     * shared event detail.
     */
    #[Test]
    public function typesTheEventDescriptorSubstructuresUnderGedcom551(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 DEAT\n2 CAUS Old age\n2 NOTE a 551 note\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        $event = $individual->deat[0];
        self::assertSame('Old age', $event->caus);
        self::assertCount(1, $event->note);
        self::assertSame('a 551 note', $event->note[0]->value);
        self::assertSame([], $this->tags($event->unknown));
    }

    /**
     * An attribute types its cause, restriction notice and notes alongside its value.
     */
    #[Test]
    public function typesTheAttributeDescriptorSubstructures(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OCCU Farmer\n2 CAUS Inherited\n2 RESN confidential\n2 NOTE an occupation note\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $attribute = $individual->occu[0];
        self::assertSame('Farmer', $attribute->value);
        self::assertSame('Inherited', $attribute->caus);
        self::assertSame('confidential', $attribute->resn);
        self::assertCount(1, $attribute->note);
        self::assertSame('an occupation note', $attribute->note[0]->value);
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
