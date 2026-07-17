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

/**
 * A level-0 record's schema-recognised substructure that the typed record does not model as a
 * property (e.g. `RFN` on an individual) is no longer silently dropped: the object mapper diverts
 * it — like an out-of-schema tag — onto the record's `$unknown` list as a {@see RawSubstructure},
 * closing the recognised-but-unmodelled ("point 2") silent-loss gap at the record level (#143).
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
#[UsesClass(RawSubstructure::class)]
class RecognisedUnmodelledPreservationTest extends TestCase
{
    /**
     * A recognised-but-unmodelled `RFN` is preserved on `$unknown` with its tag and value.
     */
    #[Test]
    public function preservesARecognisedButUnmodelledRecordChild(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 RFN ID-1\n0 TRLR\n"
        )->individuals[0];

        $byTag = $this->byTag($individual->unknown);
        self::assertArrayHasKey('RFN', $byTag);
        self::assertSame('ID-1', $byTag['RFN']->value);
    }

    /**
     * The whole subtree beneath a recognised-but-unmodelled tag is preserved verbatim, including a
     * nested child the tag's own grammar does not define.
     */
    #[Test]
    public function preservesTheNestedSubtreeOfAnUnmodelledChild(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 RFN ID-1\n2 TYPE user\n0 TRLR\n"
        )->individuals[0];

        $refn = $this->byTag($individual->unknown)['RFN'];
        self::assertSame('ID-1', $refn->value);
        self::assertSame('TYPE', $refn->children[0]->tag);
        self::assertSame('user', $refn->children[0]->value);
    }

    /**
     * Out-of-schema preservation still works alongside the new recognised-but-unmodelled one.
     */
    #[Test]
    public function preservesOutOfSchemaAndUnmodelledTogether(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 RFN ID-1\n1 _CUSTOM extension\n0 TRLR\n"
        )->individuals[0];

        $byTag = $this->byTag($individual->unknown);
        self::assertSame('ID-1', $byTag['RFN']->value);
        self::assertSame('extension', $byTag['_CUSTOM']->value);
    }

    /**
     * A modelled child is still consumed as its typed property, not diverted to `$unknown`.
     */
    #[Test]
    public function stillTypesAModelledChild(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 SEX M\n1 BIRT\n2 DATE 1 JAN 1900\n0 TRLR\n"
        )->individuals[0];

        self::assertSame('M', $individual->sex);
        self::assertCount(1, $individual->birt);
        self::assertSame([], $individual->unknown);
    }

    /**
     * A recognised-but-unmodelled tag stays at the level it occurs: a record-level child lands on
     * the record's `$unknown`, not on a nested object's (the nested-level case is covered by
     * {@see NestedUnmodelledPreservationTest}).
     */
    #[Test]
    public function preservesARecordChildOnTheRecordNotANestedObject(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 RFN ID-1\n1 BIRT\n2 DATE 1 JAN 1900\n0 TRLR\n"
        )->individuals[0];

        self::assertArrayHasKey('RFN', $this->byTag($individual->unknown));
        self::assertSame([], $individual->birt[0]->unknown);
    }

    /**
     * Every occurrence of an unmodelled tag is preserved distinctly on `$unknown`, even when the tag
     * repeats, rather than collapsed to one.
     */
    #[Test]
    public function preservesEveryOccurrenceOfARepeatedUnmodelledTag(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 RFN ID-1\n1 RFN ID-2\n0 TRLR\n"
        )->individuals[0];

        $references = [];

        foreach ($individual->unknown as $substructure) {
            if ($substructure->tag === 'RFN') {
                $references[] = $substructure->value;
            }
        }

        self::assertSame(['ID-1', 'ID-2'], $references);
    }

    /**
     * Indexes preserved substructures by their tag for assertion.
     *
     * @param list<RawSubstructure> $unknown The preserved substructures.
     *
     * @return array<string, RawSubstructure> The substructures keyed by tag.
     */
    private function byTag(array $unknown): array
    {
        $byTag = [];

        foreach ($unknown as $substructure) {
            $byTag[$substructure->tag] = $substructure;
        }

        return $byTag;
    }

    /**
     * Parses the given individual body into the typed document.
     *
     * @param string $body The GEDCOM records after the header.
     *
     * @return GedcomDocument The parsed document.
     */
    private function parse(string $body): GedcomDocument
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n" . $body;

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
