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
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\Substructure\Common\UserReference;
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
 * The records now type their user reference numbers (`REFN`) — the submitter-assigned record
 * identifiers — as typed {@see UserReference} objects rather than leaving them on `$unknown` (#132,
 * additive roll-out). Each reference keeps its value and its optional originating-system type (the
 * `REFN`.`TYPE` substructure), and the tag repeats, so the record exposes a list.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(FamilyRecord::class)]
#[CoversClass(UserReference::class)]
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
class UserReferenceTest extends TestCase
{
    /**
     * An individual's user references are typed with their value and originating-system type; the
     * tag repeats, and an unmodelled child stays on the reference's own `$unknown`.
     */
    #[Test]
    public function typesAnIndividualsUserReferences(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 REFN ID-1\n2 TYPE mysystem\n2 _CUSTOM extension\n1 REFN ID-2\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(2, $individual->refn);
        self::assertSame('ID-1', $individual->refn[0]->value);
        self::assertSame('mysystem', $individual->refn[0]->type);
        self::assertSame(['_CUSTOM'], $this->tags($individual->refn[0]->unknown));
        self::assertSame('ID-2', $individual->refn[1]->value);
        self::assertNull($individual->refn[1]->type);
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A GEDCOM 5.5.1 family user reference keeps its value and its `REFN`.`TYPE` originating system.
     */
    #[Test]
    public function typesA551FamilyUserReference(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 REFN R-9\n2 TYPE legacy\n0 TRLR\n",
            '5.5.1'
        )->families[0];

        self::assertCount(1, $family->refn);
        self::assertSame('R-9', $family->refn[0]->value);
        self::assertSame('legacy', $family->refn[0]->type);
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
