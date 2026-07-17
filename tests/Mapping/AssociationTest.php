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
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\Substructure\Common\Association;
use MagicSunday\Gedcom\Model\Substructure\Common\Role;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
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
 * The individual and family records now type their associations (`ASSO`) — a relationship to another
 * individual not captured by the family structures — as typed {@see Association} objects rather than
 * leaving them on `$unknown` (#132, additive roll-out). Both version forms are lossless: the GEDCOM
 * 5.5.1 free-text relationship (`RELA`) and the GEDCOM 7.0 enumerated role (`ROLE`) with its optional
 * phrase, plus the association's own notes and source citations.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(IndividualRecord::class)]
#[CoversClass(FamilyRecord::class)]
#[CoversClass(Association::class)]
#[CoversClass(Role::class)]
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
#[UsesClass(Note::class)]
#[UsesClass(SourceCitation::class)]
#[UsesClass(RawSubstructure::class)]
class AssociationTest extends TestCase
{
    /**
     * A GEDCOM 5.5.1 association carries its pointer and free-text relationship.
     */
    #[Test]
    public function typesAGedcom551Association(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ASSO @I2@\n2 RELA Godfather\n1 _CUSTOM person-1\n0 TRLR\n"
        )->individuals[0];

        self::assertCount(1, $individual->asso);
        self::assertSame('I2', $individual->asso[0]->xref);
        self::assertSame('Godfather', $individual->asso[0]->rela);
        self::assertNull($individual->asso[0]->role);
        self::assertSame(['_CUSTOM'], $this->tags($individual->unknown));
    }

    /**
     * A GEDCOM 7.0 association carries its pointer, enumerated role, shared-note pointer, notes and
     * source citations; an unmodelled child is preserved on the association's OWN `$unknown`, not on
     * the record's.
     */
    #[Test]
    public function typesAGedcom70Association(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 ASSO @I2@\n2 ROLE GODP\n2 NOTE a note\n2 SOUR a citation\n"
            . "2 SNOTE @N1@\n2 _CUSTOM extension\n"
            . "0 @I2@ INDI\n0 @N1@ SNOTE A shared note\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertCount(1, $individual->asso);
        $association = $individual->asso[0];
        self::assertSame('I2', $association->xref);
        self::assertSame('GODP', $association->role?->value);
        self::assertNull($association->rela);
        self::assertCount(1, $association->note);
        self::assertSame('a note', $association->note[0]->value);
        self::assertCount(1, $association->sour);
        self::assertSame('a citation', $association->sour[0]->value);
        self::assertSame(['N1'], $association->snote);

        // The extension tag is preserved on the association's own $unknown, not leaked to the record.
        self::assertSame(['_CUSTOM'], $this->tags($association->unknown));
        self::assertSame([], $this->tags($individual->unknown));
    }

    /**
     * A GEDCOM 7.0 family association is typed too, including the `@VOID@` placeholder pointer, the
     * phrase qualifying the pointer, and the role's own nested phrase.
     */
    #[Test]
    public function typesAGedcom70FamilyAssociation(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 ASSO @VOID@\n2 PHRASE an unrecorded person\n2 ROLE OTHER\n3 PHRASE the officiant\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertCount(1, $family->asso);
        $association = $family->asso[0];
        self::assertSame('VOID', $association->xref);
        // The level-2 PHRASE qualifies the association pointer.
        self::assertSame('an unrecorded person', $association->phrase);
        // The level-3 PHRASE qualifies the ROLE enumeration.
        self::assertNotNull($association->role);
        self::assertSame('OTHER', $association->role->value);
        self::assertSame('the officiant', $association->role->phrase);
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
