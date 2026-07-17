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
use MagicSunday\Gedcom\Model\Substructure\Common\AliasLink;
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
 * The individual record now types its aliases (`ALIA`) — the cross-references to other individual
 * records describing the same person — as typed {@see AliasLink} objects rather than leaving them on
 * `$unknown` (#132, additive roll-out). The GEDCOM 5.5.1 form is a bare cross-reference pointer,
 * while the GEDCOM 7.0 form additionally carries a free-text {@see AliasLink::$phrase}. Both round-trip
 * through the same typed list.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(AliasLink::class)]
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
#[UsesClass(RawSubstructure::class)]
class AliasTest extends TestCase
{
    /**
     * A GEDCOM 5.5.1 alias is a bare cross-reference pointer to another individual, with no
     * substructures — it must still be typed as an {@see AliasLink} carrying that pointer rather than
     * being diverted to `$unknown` or collapsed to a bare string.
     */
    #[Test]
    public function typesA551AliasBarePointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ALIA @I2@\n1 ALIA @I3@\n0 @I2@ INDI\n0 @I3@ INDI\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        self::assertCount(2, $individual->alia);
        self::assertSame('I2', $individual->alia[0]->xref);
        self::assertNull($individual->alia[0]->value);
        self::assertNull($individual->alia[0]->phrase);
        self::assertSame('I3', $individual->alia[1]->xref);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * Some GEDCOM 5.5.1 files misuse `ALIA` to carry a free-text alternate name rather than the
     * spec-mandated cross-reference pointer. The tolerant parser must preserve that text in
     * {@see AliasLink::$value} rather than fail to hydrate the now-typed alias — the record is kept,
     * not dropped or aborted.
     */
    #[Test]
    public function toleratesA551TextAlias(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ALIA Jupiter Indiges\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        self::assertCount(1, $individual->alia);
        self::assertNull($individual->alia[0]->xref);
        self::assertSame('Jupiter Indiges', $individual->alia[0]->value);
        self::assertNull($individual->alia[0]->phrase);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A GEDCOM 7.0 alias keeps its cross-reference pointer and its optional free-text phrase; an
     * unmodelled child stays on the alias's own `$unknown`.
     */
    #[Test]
    public function typesA70AliasWithPhrase(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ALIA @I2@\n2 PHRASE also known as\n2 _CUSTOM extension\n0 @I2@ INDI\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->alia);
        self::assertSame('I2', $individual->alia[0]->xref);
        self::assertSame('also known as', $individual->alia[0]->phrase);
        self::assertSame(['_CUSTOM'], $this->tags($individual->alia[0]->unknown));
        self::assertSame([], $this->tags($individual->unknown));
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
