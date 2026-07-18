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
     * A date keeps a substructure its grammar does not read, while the phrase it does read is
     * consumed.
     */
    #[Test]
    public function preservesTheDateSubstructuresTheGrammarDoesNotRead(): void
    {
        $date = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 DATE 1 JAN 1900\n3 PHRASE around new year\n3 TIME 12:00\n0 TRLR\n",
            '7.0'
        )->individuals[0]->birt[0]->date;

        self::assertNotNull($date);
        self::assertSame('around new year', $date->phrase, 'The phrase is the grammar’s own input.');
        self::assertSame(['TIME'], $this->tags($date->unknown));
        self::assertSame('12:00', $date->unknown[0]->value);
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
