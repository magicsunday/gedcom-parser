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
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\SpouseToFamilyLink;
use MagicSunday\Gedcom\Model\Substructure\Common\Association;
use MagicSunday\Gedcom\Model\Substructure\Common\Role;
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
 * A structure whose payload should be a pointer keeps its substructures when the file writes
 * something else (#191).
 *
 * Such a model used to require its pointer, so a file writing free text where a cross-reference
 * belongs left the model unconstructible and the whole structure — including everything well-formed
 * beneath it — was lost. The pointer is now optional and the non-conformant text preserved alongside
 * it, which is the same tolerance the hand-written pointer value objects already applied.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Association::class)]
#[CoversClass(Note::class)]
#[CoversClass(ChildToFamilyLink::class)]
#[CoversClass(SpouseToFamilyLink::class)]
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
#[UsesClass(IndividualRecord::class)]
#[UsesClass(Role::class)]
#[UsesClass(RawSubstructure::class)]
class TolerantPointerTest extends TestCase
{
    /**
     * An association written with free text instead of a pointer keeps the text and the role beneath
     * it, where the whole association used to disappear.
     */
    #[Test]
    public function keepsAnAssociationWrittenWithoutAPointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ASSO not-a-pointer\n2 ROLE GODP\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->asso, 'The association survives its malformed pointer.');
        self::assertNull($individual->asso[0]->xref);
        self::assertSame('not-a-pointer', $individual->asso[0]->value);
        self::assertSame('GODP', $individual->asso[0]->role?->value, 'The well-formed role beneath it survives too.');
        self::assertSame([], $individual->asso[0]->unknown, 'The payload is not preserved a second time.');
    }

    /**
     * A well-formed association is unaffected.
     */
    #[Test]
    public function keepsAWellFormedAssociationUnchanged(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ASSO @I2@\n2 ROLE GODP\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertSame('I2', $individual->asso[0]->xref);
        self::assertNull($individual->asso[0]->value, 'A pointer does not populate the text value.');
        self::assertSame('GODP', $individual->asso[0]->role?->value);
    }

    /**
     * The child-to-family link is tolerant on the same terms, keeping the pedigree beneath it.
     */
    #[Test]
    public function keepsAChildToFamilyLinkWrittenWithoutAPointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 FAMC not-a-pointer\n2 PEDI BIRTH\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->famc);
        self::assertNull($individual->famc[0]->xref);
        self::assertSame('not-a-pointer', $individual->famc[0]->value);
        self::assertSame('BIRTH', $individual->famc[0]->pedi);
    }

    /**
     * So is the spouse-to-family link, under GEDCOM 5.5.1 as well.
     */
    #[Test]
    public function keepsASpouseToFamilyLinkWrittenWithoutAPointer(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 FAMS not-a-pointer\n1 FAMS @F2@\n0 TRLR\n",
            '5.5.1'
        )->individuals[0];

        self::assertCount(2, $individual->fams);
        self::assertNull($individual->fams[0]->xref);
        self::assertSame('not-a-pointer', $individual->fams[0]->value);
        self::assertSame('F2', $individual->fams[1]->xref, 'The valid sibling link is unaffected.');
    }

    /**
     * A note written as a pointer to a shared note keeps that pointer apart from its text, so a note
     * whose text happens to look like a cross-reference is no longer mistaken for one.
     */
    #[Test]
    public function tellsANotePointerApartFromNoteText(): void
    {
        $document = $this->parse(
            "0 @I1@ INDI\n1 NOTE @N1@\n1 NOTE N1\n1 NOTE Some prose\n0 @N1@ NOTE Shared\n0 TRLR\n",
            '5.5.1'
        );

        $notes = $document->individuals[0]->note;
        self::assertCount(3, $notes);

        self::assertSame('N1', $notes[0]->xref, 'The pointer form names the shared note it refers to.');
        self::assertSame('N1', $notes[0]->value, 'The existing value keeps carrying the pointer target.');
        self::assertNull($notes[1]->xref, 'Text that merely looks like a cross-reference is not one.');
        self::assertSame('N1', $notes[1]->value);
        self::assertNull($notes[2]->xref);
        self::assertSame('Some prose', $notes[2]->value);
    }

    /**
     * The pointer shaping is keyed on the structure the child resolves to, not on how its line
     * happens to look: a date or age whose value reads like a cross-reference keeps that value,
     * since its own grammar is what parses it.
     */
    #[Test]
    public function keepsThePayloadOfALeafWhoseValueLooksLikeAPointer(): void
    {
        $event = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 DATE @X1@\n2 AGE @A1@\n0 TRLR\n",
            '5.5.1'
        )->individuals[0]->birt[0];

        self::assertSame('X1', $event->date?->raw, 'The date keeps its payload.');
        self::assertSame('A1', $event->age?->raw, 'So does the age.');
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
