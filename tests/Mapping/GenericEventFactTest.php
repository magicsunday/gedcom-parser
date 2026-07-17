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
use MagicSunday\Gedcom\Model\FamilyRecord;
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
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * The individual and family records now type their generic events (`EVEN`) and generic facts
 * (`FACT`) — the catch-all custom event/attribute tags — as typed {@see EventDetail} and
 * {@see AttributeDetail} lists rather than leaving them on `$unknown` (#132, #168). The individual
 * already modelled its `FACT`; this adds `EVEN` there and both to the family record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(FamilyRecord::class)]
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
#[UsesClass(AttributeDetail::class)]
#[UsesClass(DateValue::class)]
#[UsesClass(RawSubstructure::class)]
class GenericEventFactTest extends TestCase
{
    /**
     * An individual's generic event is typed as an EventDetail carrying its classification and date.
     */
    #[Test]
    public function typesAnIndividualGenericEvent(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 EVEN Graduated college\n2 TYPE Graduation\n2 DATE 1990\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->even);
        self::assertSame('Graduated college', $individual->even[0]->value);
        self::assertSame('Graduation', $individual->even[0]->type);
        self::assertSame('1990', $individual->even[0]->date?->raw);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A family's generic events and facts are typed too.
     */
    #[Test]
    public function typesFamilyGenericEventsAndFacts(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 EVEN Family reunion\n2 TYPE Reunion\n2 DATE 2000\n1 FACT Endogamous\n2 TYPE Relation\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertCount(1, $family->even);
        self::assertSame('Family reunion', $family->even[0]->value);
        self::assertSame('Reunion', $family->even[0]->type);
        self::assertSame('2000', $family->even[0]->date?->raw);
        self::assertCount(1, $family->fact);
        self::assertSame('Endogamous', $family->fact[0]->value);
        self::assertSame('Relation', $family->fact[0]->type);
        self::assertSame([], $this->tags($family->unknown));
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
