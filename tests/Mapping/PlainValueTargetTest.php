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
use MagicSunday\Gedcom\Model\ChildToFamilyLink;
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\Substructure\Common\Pedigree;
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
 * A tag whose target property holds a plain value keeps that value even when the schema gives the tag
 * substructures of its own (#185, #183).
 *
 * GEDCOM 7.0 lets a free-text phrase qualify many a value GEDCOM 5.5.1 wrote bare — a family's
 * pointers to its members, a child-to-family pedigree. Shaping those into objects the target property
 * cannot accept used to discard the value outright: a GEDCOM 7.0 family came back with no husband, no
 * wife and children reduced to the literal string `Array`. The value is now kept, and the qualifier
 * it carries is preserved on the container's `$unknown` rather than consumed.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomObjectMapper::class)]
#[CoversClass(Pedigree::class)]
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
#[UsesClass(FamilyRecord::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(ChildToFamilyLink::class)]
#[UsesClass(RawSubstructure::class)]
class PlainValueTargetTest extends TestCase
{
    /**
     * A GEDCOM 7.0 family exposes its husband, its wife and every child.
     */
    #[Test]
    public function keepsTheFamilyMembersOfAGedcom7Family(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 HUSB @I1@\n1 WIFE @I2@\n1 CHIL @I3@\n1 CHIL @I4@\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertSame('I1', $family->husb);
        self::assertSame('I2', $family->wife);
        self::assertSame(['I3', 'I4'], $family->chil, 'Every child pointer survives as a pointer.');
        self::assertSame([], $this->tags($family->unknown));
    }

    /**
     * The same family maps identically under GEDCOM 5.5.1, where the tags carry no substructures.
     */
    #[Test]
    public function keepsTheFamilyMembersOfAGedcom551Family(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 HUSB @I1@\n1 WIFE @I2@\n1 CHIL @I3@\n1 CHIL @I4@\n0 TRLR\n",
            '5.5.1'
        )->families[0];

        self::assertSame('I1', $family->husb);
        self::assertSame('I2', $family->wife);
        self::assertSame(['I3', 'I4'], $family->chil);
    }

    /**
     * The phrase GEDCOM 7.0 lets qualify such a pointer is preserved rather than consumed: the
     * pointer keeps its value, and the qualifier is carried on the family's own `$unknown`.
     */
    #[Test]
    public function preservesThePhraseQualifyingAFamilyMember(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 HUSB @I1@\n2 PHRASE the groom\n1 WIFE @I2@\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertSame('I1', $family->husb, 'The pointer is not sacrificed to its qualifier.');
        self::assertSame('I2', $family->wife);

        self::assertSame(['HUSB'], $this->tags($family->unknown));
        self::assertSame('I1', $family->unknown[0]->xref, 'The carrier names the occurrence it qualifies.');
        self::assertSame(['PHRASE'], $this->tags($family->unknown[0]->children));
        self::assertSame('the groom', $family->unknown[0]->children[0]->value);
    }

    /**
     * Where the property is a list, the qualifier stays attributable: each carrier names the entry
     * it belongs to, so a phrase on one child of several is not left floating.
     */
    #[Test]
    public function keepsAQualifierAttributableToItsChild(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 CHIL @I3@\n2 PHRASE the eldest\n1 CHIL @I4@\n1 CHIL @I5@\n2 PHRASE the youngest\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertSame(['I3', 'I4', 'I5'], $family->chil);

        $carriers = $family->unknown;
        self::assertSame(['CHIL', 'CHIL'], $this->tags($carriers));

        // The child without a phrase produces no carrier, and each carrier names its own child.
        self::assertSame('I3', $carriers[0]->xref);
        self::assertSame('the eldest', $carriers[0]->children[0]->value);
        self::assertSame('I5', $carriers[1]->xref);
        self::assertSame('the youngest', $carriers[1]->children[0]->value);
    }

    /**
     * A GEDCOM 7.0 pedigree types, where the same shaping used to discard it.
     */
    #[Test]
    public function keepsTheGedcom7Pedigree(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 FAMC @F1@\n2 PEDI BIRTH\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertSame('BIRTH', $individual->famc[0]->pedi?->value);
        self::assertSame('F1', $individual->famc[0]->xref);
    }

    /**
     * The phrase GEDCOM 7.0 permits on the pedigree is typed alongside the value it qualifies, so
     * neither is left on `$unknown` (#183).
     */
    #[Test]
    public function typesThePhraseQualifyingAPedigree(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 FAMC @F1@\n2 PEDI BIRTH\n3 PHRASE born into the family\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $link = $individual->famc[0];

        $pedigree = $link->pedi;
        self::assertInstanceOf(Pedigree::class, $pedigree);
        self::assertSame('BIRTH', $pedigree->value);
        self::assertSame('born into the family', $pedigree->phrase);
        self::assertSame([], $this->tags($link->unknown), 'Nothing is left over once both are typed.');
    }

    /**
     * The GEDCOM 5.5.1 pedigree is unaffected.
     */
    #[Test]
    public function keepsTheGedcom551Pedigree(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 FAMC @F1@\n2 PEDI birth\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        self::assertSame('birth', $individual->famc[0]->pedi?->value);
    }

    /**
     * A pedigree outside the enumerated set survives parsing with its qualifier, so an extension is
     * kept rather than rejected or diverted.
     */
    #[Test]
    public function toleratesAnUnlistedPedigree(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 FAMC @F1@\n2 PEDI _CUSTOM\n3 PHRASE a bespoke linkage\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        $pedigree = $individual->famc[0]->pedi;

        self::assertInstanceOf(Pedigree::class, $pedigree);
        self::assertSame('_CUSTOM', $pedigree->value);
        self::assertSame('a bespoke linkage', $pedigree->phrase);
        self::assertSame([], $this->tags($individual->famc[0]->unknown));
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
