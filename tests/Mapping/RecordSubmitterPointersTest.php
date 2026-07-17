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
 * The individual and family records now type their submitter and research-interest cross-reference
 * pointers — the submitter (`SUBM`) both records carry, plus the ancestor-interest (`ANCI`) and
 * descendant-interest (`DESI`) pointers of an individual — as `list<string>` of cross-references,
 * the same shape the other record pointers (HUSB/WIFE/CHIL) already use, rather than leaving them on
 * `$unknown` (#132, additive roll-out). Each is a pure pointer with no substructure, so the
 * cross-reference list is lossless in both GEDCOM 5.5.1 and 7.0.
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
#[UsesClass(RawSubstructure::class)]
class RecordSubmitterPointersTest extends TestCase
{
    /**
     * An individual types its submitter and research-interest pointers; an unmodelled extension tag
     * (`_CUSTOM`) stays on `$unknown`.
     */
    #[Test]
    public function typesAnIndividualsSubmitterAndInterestPointers(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 SUBM @U1@\n1 ANCI @U2@\n1 DESI @U3@\n1 _CUSTOM person-1\n0 TRLR\n"
        )->individuals[0];

        self::assertSame(['U1'], $individual->subm);
        self::assertSame(['U2'], $individual->anci);
        self::assertSame(['U3'], $individual->desi);
        self::assertSame(['_CUSTOM'], $this->tags($individual->unknown));
    }

    /**
     * A family types its submitter pointers, and repeats ({0:M}) preserve every cross-reference.
     */
    #[Test]
    public function typesAFamilysSubmitterPointers(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 SUBM @U1@\n1 SUBM @U2@\n0 TRLR\n"
        )->families[0];

        self::assertSame(['U1', 'U2'], $family->subm);
        self::assertSame([], $this->tags($family->unknown));
    }

    /**
     * The pointers type identically under GEDCOM 7.0.
     */
    #[Test]
    public function typesTheSubmitterPointersUnderGedcom70(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 SUBM @U1@\n1 ANCI @U2@\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertSame(['U1'], $individual->subm);
        self::assertSame(['U2'], $individual->anci);
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
