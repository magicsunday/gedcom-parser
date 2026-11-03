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
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\MapCoordinates;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function array_keys;
use function array_map;
use function array_values;

/**
 * A substructure a value-object leaf's own grammar has no use for is preserved on that leaf's
 * `$unknown` rather than dropped (#179).
 *
 * Such a leaf — a place, a date, an age — is shaped without knowing its target class, so the mapper
 * cannot read off a model which of its children the handler will consume; the handler's consumed
 * tags are named explicitly instead. Everything else it carries is diverted, so the GEDCOM 7.0
 * language, translations, notes and identifiers a place may bear survive, while the tags the grammar
 * does read — a place's `FORM` and `MAP`, a date's `PHRASE` — are still its input and never diverted.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomObjectMapper::class)]
#[CoversClass(JsonMapperFactory::class)]
#[UsesClass(Parser::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(Reader::class)]
#[UsesClass(GedcomDocumentReader::class)]
#[UsesClass(GedcomVersionDetector::class)]
#[UsesClass(RecordStream::class)]
#[UsesClass(GedcomTreeReader::class)]
#[UsesClass(GedcomNode::class)]
#[UsesClass(RegistrySchemaLoader::class)]
#[UsesClass(Schema::class)]
#[UsesClass(GedcomVersion::class)]
#[UsesClass(GedcomDocument::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(PlaceValue::class)]
#[UsesClass(MapCoordinates::class)]
#[UsesClass(AgeValue::class)]
#[UsesClass(RawSubstructure::class)]
class LeafSubstructurePreservationTest extends TestCase
{
    /**
     * A GEDCOM 7.0 place keeps the substructures its grammar does not read.
     */
    #[Test]
    public function preservesThePlaceSubstructuresTheGrammarDoesNotRead(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston, Suffolk\n3 LANG en\n3 NOTE A place note\n3 EXID 12345\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(['Boston', 'Suffolk'], $place->levels);
        self::assertSame(['LANG', 'NOTE', 'EXID'], $this->tags($place->unknown));
        self::assertSame('en', $place->unknown[0]->value);
        self::assertSame('A place note', $place->unknown[1]->value);
        self::assertSame('12345', $place->unknown[2]->value);
    }

    /**
     * The tags the place grammar does read stay its input and are never diverted — were they, the
     * hierarchy form and the coordinates would be lost.
     */
    #[Test]
    public function doesNotDivertThePlaceSubstructuresTheGrammarReads(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston, Suffolk\n3 FORM City, County\n"
            . "3 MAP\n4 LATI N42.3\n4 LONG W71.0\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame('City, County', $place->form);
        self::assertInstanceOf(MapCoordinates::class, $place->coordinates);
        self::assertSame([], $this->tags($place->unknown), 'Neither FORM nor MAP is diverted.');
    }

    /**
     * A date keeps a substructure its grammar does not read, while the phrase and the time it does
     * read are consumed into typed values.
     */
    #[Test]
    public function preservesTheDateSubstructuresTheGrammarDoesNotRead(): void
    {
        $date = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 DATE 1 JAN 1900\n3 PHRASE around new year\n3 TIME 12:00\n"
            . "3 _CUSTOM Extension payload\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->date;

        self::assertNotNull($date);
        self::assertSame('around new year', $date->phrase, 'The phrase is the grammar’s own input.');
        self::assertSame('12:00', $date->time, 'So is the time, as of #189.');
        self::assertSame(['_CUSTOM'], $this->tags($date->unknown), 'Only what the grammar does not read is diverted.');
        self::assertSame('Extension payload', $date->unknown[0]->value);
    }

    /**
     * An out-of-schema extension beneath such a leaf is preserved as it always was, alongside the
     * schema-recognised ones the grammar does not read.
     */
    #[Test]
    public function preservesAnExtensionAlongsideARecognisedSubstructure(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n3 LANG en\n3 _CUSTOM Extension payload\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(['LANG', '_CUSTOM'], $this->tags($place->unknown));
    }

    /**
     * A GEDCOM 5.5.1 place declares substructures of its own too — a phonetic and a romanised
     * rendering alongside notes — and they are preserved on the same terms.
     */
    #[Test]
    public function preservesTheGedcom551PlaceSubstructuresTheGrammarDoesNotRead(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston, Suffolk\n3 FORM City, County\n"
            . "3 MAP\n4 LATI N42.3\n4 LONG W71.0\n3 NOTE A place note\n3 FONE Boston\n3 ROMN Boston\n0 TRLR\n",
            '5.5.1'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(['Boston', 'Suffolk'], $place->levels);

        // What the grammar reads stays its input …
        self::assertSame('City, County', $place->form);
        self::assertInstanceOf(MapCoordinates::class, $place->coordinates);

        // … and what it does not is kept rather than dropped.
        self::assertSame(['NOTE', 'FONE', 'ROMN'], $this->tags($place->unknown));
        self::assertSame('A place note', $place->unknown[0]->value);
    }

    /**
     * A preserved substructure keeps its own subtree and its pointer form: a GEDCOM 7.0 translation
     * carries a required language of its own, and a shared-note child is a cross-reference.
     */
    #[Test]
    public function preservesTheSubtreeAndPointerOfADivertedSubstructure(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n3 TRAN Bostonas\n4 LANG lt\n3 SNOTE @N1@\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(['TRAN', 'SNOTE'], $this->tags($place->unknown));

        self::assertSame('Bostonas', $place->unknown[0]->value);
        self::assertSame(['LANG'], $this->tags($place->unknown[0]->children));
        self::assertSame('lt', $place->unknown[0]->children[0]->value);

        self::assertSame('N1', $place->unknown[1]->xref, 'A diverted pointer keeps its cross-reference.');
    }

    /**
     * The preservation reaches a leaf nested inside another: a child of the coordinates beneath a
     * place is kept on the coordinates' own `$unknown`, while the axes their grammar reads still
     * build the position.
     */
    #[Test]
    public function preservesTheCoordinateSubstructuresTheGrammarDoesNotRead(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n3 MAP\n4 LATI N42.3\n4 LONG W71.0\n"
            . "4 _SRC surveyed\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);

        $coordinates = $place->coordinates;
        self::assertInstanceOf(MapCoordinates::class, $coordinates);
        self::assertSame(42.3, $coordinates->latitude, 'The axes still reach the coordinate grammar.');
        self::assertSame(-71.0, $coordinates->longitude);

        self::assertSame(['_SRC'], $this->tags($coordinates->unknown));
        self::assertSame('surveyed', $coordinates->unknown[0]->value);

        self::assertSame([], $this->tags($place->unknown), 'A MAP the grammar could read is not diverted.');
    }

    /**
     * A `MAP` the coordinate grammar cannot read is diverted to the place whole rather than falling
     * away with the position that could not be built (#188).
     *
     * There is nowhere else for it to go: the preserved substructures of a `MAP` live on the
     * coordinates, so when no coordinates are built they need the place's own list. Each case below
     * is a distinct way the grammar can come up empty, and before this fix each lost everything the
     * `MAP` carried — the extension, the axis that WAS well-formed, and the payload alike.
     *
     * @param string                           $body     The MAP substructure as written.
     * @param string|null                      $expected The diverted MAP's own value, or NULL when
     *                                                   it carries none.
     * @param string|null                      $xref     The diverted MAP's cross-reference, or NULL
     *                                                   when the line carries none.
     * @param list<array{string, string|null}> $children The tag and value preserved beneath it.
     * @param string                           $version  The GEDCOM version to parse it under.
     */
    #[Test]
    #[DataProvider('unreadableCoordinates')]
    public function divertsACoordinateTheGrammarCannotReadToThePlace(
        string $body,
        ?string $expected,
        ?string $xref,
        array $children,
        string $version,
    ): void {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n" . $body . "0 TRLR\n",
            $version
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame('Boston', $place->levels[0], 'The place itself still maps.');
        self::assertNull($place->coordinates, 'No position could be built from it.');

        self::assertSame(['MAP'], $this->tags($place->unknown), 'The MAP is kept rather than dropped.');
        self::assertSame($expected, $place->unknown[0]->value);
        self::assertSame($xref, $place->unknown[0]->xref);

        // The tag alone would pass on a rebuild that dropped every axis payload, which is the very
        // content this preservation exists for.
        $preserved = array_map(
            static fn (RawSubstructure $child): array => [$child->tag, $child->value],
            $place->unknown[0]->children
        );

        self::assertSame($children, $preserved);
    }

    /**
     * The ways a `MAP` can fail to yield a position, each losing different content before #188.
     *
     * Each case gives the MAP body as written, the diverted MAP's own value and cross-reference, the
     * tag and value of every child preserved beneath it, and the GEDCOM version to parse it under.
     *
     * @return iterable<string, array{string, string|null, string|null, list<array{string, string|null}>, string}> The cases.
     */
    public static function unreadableCoordinates(): iterable
    {
        // A MAP carrying a payload where the axes belong. It still shapes to an array — MAP declares
        // the axes, so the shaping recurses regardless — and the payload lands under its `value`.
        yield 'a payload instead of the axes' => ["3 MAP surveyed\n", 'surveyed', null, [], '7.0'];

        // The pointer form of the same malformation, which lands under `xref` instead.
        yield 'a pointer instead of the axes' => ["3 MAP @X1@\n", null, 'X1', [], '7.0'];

        // A MAP line with nothing at all beneath it: the plainest malformed form, and the only one
        // where what is preserved is an empty MAP.
        yield 'an empty map' => ["3 MAP\n", null, null, [], '7.0'];

        // Only one axis: a position needs both, and the one that WAS given used to fall away too.
        yield 'one axis only' => ["3 MAP\n4 LATI N42.3\n", null, null, [['LATI', 'N42.3']], '7.0'];

        // The mirror of it, so neither axis key is covered only by the other's presence.
        yield 'the other axis only' => ["3 MAP\n4 LONG W71.0\n", null, null, [['LONG', 'W71.0']], '7.0'];

        // A value-less axis line carries no value, which a raw substructure spells NULL rather than
        // as the empty string the grammar helper resolves it to.
        yield 'a value-less axis' => ["3 MAP\n4 LATI\n", null, null, [['LATI', null]], '7.0'];

        // Neither axis, only an extension — the case that has no coordinates to hang it on at all.
        yield 'no axis at all' => ["3 MAP\n4 _SRC surveyed\n", null, null, [['_SRC', 'surveyed']], '7.0'];

        // Both axes present but one out of range, so the value object rejects the pair; the axes and
        // the extension beside them were all discarded together.
        yield 'an axis beyond its bounds' => [
            "3 MAP\n4 LATI N99.9\n4 LONG W71.0\n4 _SRC surveyed\n",
            null,
            null,
            [['LATI', 'N99.9'], ['LONG', 'W71.0'], ['_SRC', 'surveyed']],
            '7.0',
        ];

        // An axis bearing substructures of its own is diverted whole by the shaping, so the rebuild
        // must take that carrier rather than emit the axis a second time beside it. The carrier is
        // appended with the other preserved substructures, hence after the axis rebuilt from its key.
        yield 'an axis carrying a substructure' => [
            "3 MAP\n4 LATI N99.9\n5 _SRC gps\n4 LONG W71.0\n",
            null,
            null,
            [['LONG', 'W71.0'], ['LATI', 'N99.9']],
            '7.0',
        ];

        // The same axis given twice: the shaping keeps the first in its typed key and diverts the
        // second as a carrier, so both are present and both must survive. Skipping on the tag alone
        // would take the carrier as standing for the typed occurrence and delete a real value.
        yield 'the same axis given twice' => [
            "3 MAP\n4 LATI N42.3\n4 LATI N43.1\n5 _SRC gps\n",
            null,
            null,
            [['LATI', 'N42.3'], ['LATI', 'N43.1']],
            '7.0',
        ];

        // The 5.5.1 registry gives MAP the same shape, so the preservation must hold there too.
        yield 'the same shape under GEDCOM 5.5.1' => [
            "3 MAP\n4 LATI N99.9\n4 LONG W71.0\n4 _SRC surveyed\n",
            null,
            null,
            [['LATI', 'N99.9'], ['LONG', 'W71.0'], ['_SRC', 'surveyed']],
            '5.5.1',
        ];
    }

    /**
     * A payload on a `MAP` line is preserved even when the axes beneath it DO build a position —
     * otherwise the one part of a malformed MAP that survives when the axes fail would be dropped
     * when they succeed. Both registries give MAP the same payload-less definition, so the same must
     * hold under either.
     *
     * @param string $version The GEDCOM version to parse it under.
     */
    #[Test]
    #[DataProvider('gedcomVersions')]
    public function divertsAPayloadOnAReadableMapLine(string $version): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n3 MAP surveyed\n4 LATI N42.3\n4 LONG W71.0\n0 TRLR\n",
            $version
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);

        $coordinates = $place->coordinates;
        self::assertInstanceOf(MapCoordinates::class, $coordinates);
        self::assertSame(42.3, $coordinates->latitude, 'The axes still build the position.');

        self::assertSame(['MAP'], $this->tags($place->unknown));
        self::assertSame('surveyed', $place->unknown[0]->value);
        self::assertSame([], $place->unknown[0]->children, 'The axes were consumed, so only the payload is left.');
    }

    /**
     * A rebuilt `MAP` carries the level of the line it stood on, and its axes the level below it, so
     * one preserved subtree does not mix entries that have a level with entries that do not (#212).
     *
     * The axes here come from two different places — one rebuilt from its typed key, one diverted
     * whole as a carrier — and both must agree, which is exactly what a level read from the node
     * alone would not have given.
     */
    #[Test]
    public function givesARebuiltCoordinateTheLevelsOfTheLinesItStoodOn(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n3 MAP\n4 LATI N99.9\n5 _Q z\n4 LONG W71.0\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertNull($place->coordinates);

        $map = $place->unknown[0];
        self::assertSame('MAP', $map->tag);
        self::assertSame(3, $map->level, 'The MAP keeps the level of its own line.');

        foreach ($map->children as $axis) {
            self::assertSame(4, $axis->level, $axis->tag . ' sits one level below the MAP.');
        }

        self::assertSame(5, $map->children[1]->children[0]->level, 'The carrier keeps its subtree levels.');
    }

    /**
     * The stray-payload carrier keeps its level too. It reproduces exactly one real line, so a null
     * there would be the plainest possible loss of something the file stated.
     */
    #[Test]
    public function givesAStrayMapPayloadTheLevelOfItsLine(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n3 MAP surveyed\n4 LATI N42.3\n4 LONG W71.0\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertInstanceOf(MapCoordinates::class, $place->coordinates);

        self::assertSame('surveyed', $place->unknown[0]->value);
        self::assertSame(3, $place->unknown[0]->level);
    }

    /**
     * The GEDCOM versions whose registries define the place structure.
     *
     * @return iterable<string, array{string}> The version, keyed by itself.
     */
    public static function gedcomVersions(): iterable
    {
        yield '7.0' => ['7.0'];
        yield '5.5.1' => ['5.5.1'];
    }

    /**
     * The same holds for the pointer form of that payload, which is preserved on its own — a MAP
     * line carrying only a cross-reference is left over just as a value-carrying one is.
     */
    #[Test]
    public function divertsAPointerOnAReadableMapLine(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n3 MAP @X1@\n4 LATI N42.3\n4 LONG W71.0\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertInstanceOf(MapCoordinates::class, $place->coordinates);

        self::assertSame(['MAP'], $this->tags($place->unknown));
        self::assertSame('X1', $place->unknown[0]->xref);
        self::assertNull($place->unknown[0]->value);
        self::assertSame([], $place->unknown[0]->children);
    }

    /**
     * A diverted MAP is appended after the place's other preserved substructures rather than kept in
     * source order, which is a stated limitation of the shape rather than an accident: it records no
     * position for a child, so where the MAP stood among its siblings cannot be recovered.
     *
     * The sibling is an extension rather than a recognised tag: a recognised one would leave the
     * `$unknown` list the moment the place models it, breaking this test for a reason that has
     * nothing to do with the order it pins.
     */
    #[Test]
    public function appendsADivertedMapAfterThePlacesOtherPreservedSubstructures(): void
    {
        $place = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 PLAC Boston\n3 MAP\n4 _SRC surveyed\n3 _CUSTOM payload\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->plac;

        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(['_CUSTOM', 'MAP'], $this->tags($place->unknown), 'The MAP comes last though it came first.');
    }

    /**
     * Every value-object leaf must name the tags its grammar reads, or the mapper would divert that
     * grammar's own input away from it — the very loss this preservation exists to prevent.
     */
    #[Test]
    public function everyValueObjectLeafNamesWhatItsGrammarReads(): void
    {
        $unnamed = array_values(
            array_diff(JsonMapperFactory::LEAF_VALUE_TYPES, array_keys(JsonMapperFactory::HANDLER_CONSUMED_TAGS))
        );

        self::assertSame(
            [RawSubstructure::class],
            $unnamed,
            'Only the raw carrier may be absent; it is never the target of a shaped child.'
        );
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
    private function parse(string $body, string $version): GedcomDocument
    {
        $charset = $version === '5.5.1' ? "1 CHAR ASCII\n" : '';
        $gedcom  = "0 HEAD\n1 GEDC\n2 VERS " . $version . "\n" . $charset . $body;

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
