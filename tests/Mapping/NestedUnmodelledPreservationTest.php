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
use MagicSunday\Gedcom\Model\ChangeDate;
use MagicSunday\Gedcom\Model\ChildToFamilyLink;
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
use MagicSunday\Gedcom\ValueObject\MapCoordinates;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_keys;

/**
 * The recognised-but-unmodelled preservation now recurses into modelled nested containers: a tag the
 * schema permits under a modelled substructure (e.g. the child-to-family status `STAT`, which
 * {@see ChildToFamilyLink} does not model) is preserved on that nested object's own `$unknown` list
 * rather than dropped — while a value-object leaf (`DATE`/`PLAC`/`AGE`, hydrated by a type handler from its
 * raw payload) is NOT shaped class-aware, so its own substructures are never wrongly diverted (#143).
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
#[UsesClass(ChildToFamilyLink::class)]
#[UsesClass(EventDetail::class)]
#[UsesClass(ChangeDate::class)]
#[UsesClass(Note::class)]
#[UsesClass(PlaceValue::class)]
#[UsesClass(MapCoordinates::class)]
#[UsesClass(RawSubstructure::class)]
class NestedUnmodelledPreservationTest extends TestCase
{
    /**
     * A tag recognised under a modelled container but not modelled by it is preserved on that
     * container's own `$unknown` list, not dropped.
     */
    #[Test]
    public function preservesARecognisedButUnmodelledTagUnderAModelledContainer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 FAMC @F1@\n2 PEDI birth\n2 STAT CHALLENGED\n0 TRLR\n"
        )->individuals[0];

        self::assertCount(1, $individual->famc);
        self::assertSame([], $individual->unknown);

        // The modelled sibling must still be consumed: diverting everything would pass a bare
        // "STAT was preserved" assertion while the container had stopped typing altogether.
        self::assertSame('birth', $individual->famc[0]->pedi);

        $byTag = $this->byTag($individual->famc[0]->unknown);
        self::assertSame(['STAT'], array_keys($byTag), 'Only the unmodelled tag is diverted.');
        self::assertSame('CHALLENGED', $byTag['STAT']->value);
    }

    /**
     * The same holds under GEDCOM 7.0, where the status is a separate registry structure. The
     * modelled sibling asserted here is the pointer rather than the pedigree, because the GEDCOM 7.0
     * pedigree does not currently type at all (see issue #183).
     */
    #[Test]
    public function preservesARecognisedButUnmodelledTagUnderAModelledContainerInGedcom7(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 FAMC @F1@\n2 STAT CHALLENGED\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertSame('F1', $individual->famc[0]->xref);

        $byTag = $this->byTag($individual->famc[0]->unknown);
        self::assertSame(['STAT'], array_keys($byTag));
        self::assertSame('CHALLENGED', $byTag['STAT']->value);
    }

    /**
     * A value-object leaf under a modelled event (`PLAC`, hydrated by the PlaceValue handler) is NOT
     * shaped class-aware, so its own substructures — here `MAP` (with `LATI`/`LONG`), which the
     * handler reads to build the coordinates — are handler input, not unmodelled tags. Were the leaf
     * wrongly shaped class-aware, `MAP` would be diverted and the coordinates would be lost.
     */
    #[Test]
    public function doesNotDivertTheSubstructuresOfAValueObjectLeaf(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Berlin\n3 MAP\n4 LATI N52.5\n4 LONG E13.4\n0 TRLR\n"
        )->individuals[0];

        $place = $individual->birt[0]->plac;

        // MAP is the handler's input; if the leaf were shaped class-aware it would be diverted and
        // the coordinates would come back NULL.
        self::assertNotNull($place?->coordinates);
        self::assertSame([], $individual->birt[0]->unknown);
    }

    /**
     * A modelled container that carries its own `$unknown` list (a `NOTE`, hydrated by the Note
     * handler which reads a diverted `unknown` key) IS shaped class-aware: a tag the schema permits
     * under a note but that {@see Note} does not model (a GEDCOM 7.0 `SOUR` citation) is preserved on
     * that note's own `$unknown`, not dropped.
     */
    #[Test]
    public function preservesARecognisedButUnmodelledTagUnderAModelledNote(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 7.0\n"
            . "0 @I1@ INDI\n1 CHAN\n2 DATE 1 JAN 2020\n2 NOTE A note\n3 SOUR @S1@\n"
            . "0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n";

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        $individual = (new Parser($stream))->parse()->individuals[0];

        self::assertNotNull($individual->chan);
        self::assertCount(1, $individual->chan->note);

        $byTag = $this->byTag($individual->chan->note[0]->unknown);
        self::assertArrayHasKey('SOUR', $byTag);
        self::assertSame('S1', $byTag['SOUR']->xref);
    }

    /**
     * A modelled nested field (the event's DATE) is still typed, not diverted.
     */
    #[Test]
    public function stillTypesAModelledNestedField(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 DATE 1 JAN 1900\n0 TRLR\n"
        )->individuals[0];

        self::assertNotNull($individual->birt[0]->date);
        self::assertSame([], $individual->birt[0]->unknown);
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
     * @param string $body    The GEDCOM records after the header.
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
