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
 * The family record now types the remaining standard family event tags (divorce, engagement,
 * annulment, marriage banns/contract/licence/settlement, census) as {@see EventDetail} lists and its
 * residence attribute (RESI) as an {@see AttributeDetail} list — so a consumer navigates them typed
 * rather than reaching for `$unknown` (#132, additive roll-out). FAM.NCHI is deliberately left
 * unmodelled: its shape diverges across versions (a 5.5.1 `{0:1}` scalar count vs a 7.0 `{0:M}`
 * attribute), so it is preserved on `$unknown` pending a version-aware model.
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
#[UsesClass(EventDetail::class)]
#[UsesClass(AttributeDetail::class)]
#[UsesClass(RawSubstructure::class)]
class FamilyEventsTest extends TestCase
{
    /**
     * The standard family events are each typed onto their own EventDetail property.
     */
    #[Test]
    public function typesTheStandardFamilyEvents(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 DIV\n2 DATE 1930\n1 ENGA\n2 DATE 1919\n1 ANUL\n2 PLAC Rome\n0 TRLR\n"
        )->families[0];

        self::assertSame('1930', $family->div[0]->date?->raw);
        self::assertSame('1919', $family->enga[0]->date?->raw);
        self::assertSame('Rome', $family->anul[0]->plac?->raw);
        self::assertSame([], $this->tags($family->unknown));
    }

    /**
     * The residence attribute (RESI) is typed as an AttributeDetail, while the version-divergent
     * NCHI count is preserved on `$unknown` (not modelled in this batch).
     */
    #[Test]
    public function typesTheResidenceAttributeAndPreservesNchi(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 NCHI 4\n1 RESI\n2 PLAC Vienna\n0 TRLR\n"
        )->families[0];

        self::assertNull($family->resi[0]->value);
        self::assertSame('Vienna', $family->resi[0]->plac?->raw);
        self::assertSame(['NCHI'], $this->tags($family->unknown));
    }

    /**
     * The family events type identically under GEDCOM 7.0, and the version-divergent NCHI (a `{0:M}`
     * attribute in 7.0) is preserved on `$unknown` without a mapping error rather than mis-mapped.
     */
    #[Test]
    public function typesFamilyEventsUnderGedcom70(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 DIV\n2 DATE 1930\n1 NCHI 4\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertSame('1930', $family->div[0]->date?->raw);
        self::assertSame(['NCHI'], $this->tags($family->unknown));
    }

    /**
     * A tag still not modelled on the family (a user reference `REFN`) is preserved on `$unknown`.
     */
    #[Test]
    public function stillPreservesAnUnmodelledFamilyTag(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 REFN F-1\n0 TRLR\n"
        )->families[0];

        self::assertSame(['REFN'], $this->tags($family->unknown));
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
