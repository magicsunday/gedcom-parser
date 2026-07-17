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
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
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
 * The family record now types its child-count attribute (`FAM`.`NCHI`), which diverges across
 * versions: GEDCOM 5.5.1 carries a bare non-negative count with no substructures, while GEDCOM 7.0
 * carries a full family attribute (with its own notes, sources and other substructures). Both round-
 * trip through the same version-agnostic `list<AttributeDetail>` (#132, #148).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
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
#[UsesClass(AttributeDetail::class)]
#[UsesClass(Note::class)]
#[UsesClass(RawSubstructure::class)]
class FamilyChildCountTest extends TestCase
{
    /**
     * A GEDCOM 5.5.1 family child count is a bare non-negative integer with no substructures — it
     * must still be typed as an {@see AttributeDetail} carrying that count rather than being diverted
     * to `$unknown` or collapsed to a bare string.
     */
    #[Test]
    public function typesA551BareChildCount(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 NCHI 3\n0 TRLR\n",
            '5.5.1'
        )->families[0];

        self::assertCount(1, $family->nchi);
        self::assertSame('3', $family->nchi[0]->value);
        self::assertSame([], $this->tags($family->unknown));
    }

    /**
     * A GEDCOM 7.0 family child count is a full family attribute carrying its own count value plus
     * substructures; the count round-trips into the same typed list, and an unmodelled child stays on
     * the attribute's own `$unknown`.
     */
    #[Test]
    public function typesA70ChildCountAttribute(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 NCHI 4\n2 TYPE stepchildren\n2 _CUSTOM extension\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertCount(1, $family->nchi);
        self::assertSame('4', $family->nchi[0]->value);
        self::assertSame('stepchildren', $family->nchi[0]->type);
        self::assertSame(['_CUSTOM'], $this->tags($family->nchi[0]->unknown));
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
