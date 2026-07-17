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
 * The individual and family records now type their restriction notice (`RESN`) as a tolerant string
 * and their GEDCOM 7.0 shared-note pointers (`SNOTE`) as a `list<string>` of cross-references, rather
 * than leaving them on `$unknown` (#132, additive roll-out).
 *
 * The restriction value is preserved verbatim: GEDCOM 5.5.1 writes it lower-case (`confidential`) and
 * GEDCOM 7.0 upper-case (`CONFIDENTIAL`), and neither is normalised, so a value from either version —
 * or an extension value — survives unchanged. The shared-note pointer exists only in GEDCOM 7.0.
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
class RecordRestrictionAndSharedNoteTest extends TestCase
{
    /**
     * An individual types its restriction notice, preserving the GEDCOM 5.5.1 lower-case value
     * verbatim; an unmodelled extension tag (`_CUSTOM`) stays on `$unknown`.
     */
    #[Test]
    public function typesAnIndividualsRestriction(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 RESN confidential\n1 _CUSTOM person-1\n0 TRLR\n"
        )->individuals[0];

        self::assertSame('confidential', $individual->resn);
        self::assertSame(['_CUSTOM'], $this->tags($individual->unknown));
    }

    /**
     * A family types its restriction notice.
     */
    #[Test]
    public function typesAFamilysRestriction(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 RESN locked\n0 TRLR\n"
        )->families[0];

        self::assertSame('locked', $family->resn);
        self::assertSame([], $this->tags($family->unknown));
    }

    /**
     * Under GEDCOM 7.0 the restriction value is preserved in its upper-case form (not normalised to
     * the 5.5.1 lower-case), and the shared-note pointers of both records are typed as cross-reference
     * lists.
     */
    #[Test]
    public function typesTheRestrictionAndSharedNotePointersUnderGedcom70(): void
    {
        $document = $this->parse(
            "0 @I1@ INDI\n1 RESN CONFIDENTIAL\n1 SNOTE @N1@\n1 SNOTE @N2@\n"
            . "0 @F1@ FAM\n1 SNOTE @N1@\n"
            . "0 @N1@ SNOTE A shared note\n0 @N2@ SNOTE Another shared note\n0 TRLR\n",
            '7.0'
        );

        $individual = $document->individuals[0];
        self::assertSame('CONFIDENTIAL', $individual->resn);
        self::assertSame(['N1', 'N2'], $individual->snote);
        self::assertSame([], $this->tags($individual->unknown));

        $family = $document->families[0];
        self::assertSame(['N1'], $family->snote);
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
