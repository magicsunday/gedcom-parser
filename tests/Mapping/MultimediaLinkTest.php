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
 * The individual and family records now type their multimedia links (`OBJE`) as typed
 * {@see MultimediaLink} objects rather than leaving them on `$unknown` (#132, additive roll-out). The
 * pointer form keeps its multimedia-record cross-reference and its optional overriding title; the
 * GEDCOM 5.5.1 inline form (an embedded `FILE`/`FORM` block) and the GEDCOM 7.0 `CROP` subregion are
 * not yet typed and are preserved verbatim on the link's own `$unknown` (see the follow-up).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(FamilyRecord::class)]
#[CoversClass(MultimediaLink::class)]
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
class MultimediaLinkTest extends TestCase
{
    /**
     * A GEDCOM 7.0 multimedia link keeps its multimedia-record pointer and its overriding title; the
     * not-yet-modelled `CROP` subregion is preserved verbatim on the link's own `$unknown` (deferred
     * to the follow-up) rather than dropped.
     */
    #[Test]
    public function typesA70IndividualMultimediaLink(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OBJE @M1@\n2 TITL Portrait\n2 CROP\n3 TOP 10\n3 LEFT 20\n0 @M1@ OBJE\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->obje);
        self::assertSame('M1', $individual->obje[0]->xref);
        self::assertSame('Portrait', $individual->obje[0]->titl);
        self::assertSame(['CROP'], $this->tags($individual->obje[0]->unknown));
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A GEDCOM 5.5.1 inline multimedia object (an embedded `FILE`/`FORM` block rather than a pointer)
     * still types its overriding title, and the not-yet-modelled inline block is preserved verbatim
     * on the link's `$unknown` rather than dropped.
     */
    #[Test]
    public function preservesA551InlineMultimediaObject(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 OBJE\n2 FILE photo.jpg\n3 FORM jpeg\n2 TITL Old Photo\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        self::assertCount(1, $individual->obje);
        self::assertNull($individual->obje[0]->xref);
        self::assertSame('Old Photo', $individual->obje[0]->titl);
        self::assertSame(['FILE'], $this->tags($individual->obje[0]->unknown));
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A family's multimedia link is typed too.
     */
    #[Test]
    public function typesAFamilyMultimediaLink(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 OBJE @M2@\n0 @M2@ OBJE\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertCount(1, $family->obje);
        self::assertSame('M2', $family->obje[0]->xref);
        self::assertNull($family->obje[0]->titl);
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
