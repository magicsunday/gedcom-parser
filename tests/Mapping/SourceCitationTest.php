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
use MagicSunday\Gedcom\Model\EventDetail;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\Substructure\Common\SourceCitationEvent;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitationData;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * A source citation (`SOUR` under an event) is now typed as a {@see SourceCitation}: the pointer
 * citation carries its `PAGE`, and its `->source()` lazily resolves the referenced
 * {@see SourceRecord} through the document's cross-reference index — the first typed pointer with
 * lazy resolution (#132, increment 1).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(SourceCitation::class)]
#[CoversClass(GedcomDocument::class)]
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
#[UsesClass(IndividualRecord::class)]
#[UsesClass(EventDetail::class)]
#[UsesClass(SourceRecord::class)]
#[UsesClass(Note::class)]
#[UsesClass(SourceCitationData::class)]
#[UsesClass(SourceCitationEvent::class)]
class SourceCitationTest extends TestCase
{
    /**
     * A pointer source citation under a birth event carries its PAGE and resolves to the referenced
     * source record via the document's index.
     */
    #[Test]
    public function aPointerCitationResolvesToItsSourceRecord(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 PAGE p. 42\n"
            . "0 @S1@ SOUR\n1 TITL A cited source\n0 TRLR\n";

        $document = $this->parse($gedcom);

        self::assertCount(1, $document->individuals);
        self::assertCount(1, $document->individuals[0]->birt);

        $citations = $document->individuals[0]->birt[0]->sour;
        self::assertCount(1, $citations);

        $citation = $citations[0];
        self::assertSame('p. 42', $citation->page);

        $source = $citation->source($document);
        self::assertInstanceOf(SourceRecord::class, $source);
        self::assertSame('S1', $source->xref);
    }

    /**
     * A citation carries its transcribed DATA (the generated {@see SourceCitationData}): its TEXT
     * lines and its DATE.
     */
    #[Test]
    public function aCitationCarriesItsTypedData(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 DATA\n4 DATE 1 JAN 1900\n4 TEXT a transcribed line\n"
            . "0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n";

        $document = $this->parse($gedcom);

        $citation = $document->individuals[0]->birt[0]->sour[0];

        self::assertNotNull($citation->data);
        self::assertCount(1, $citation->data->text);
        self::assertSame('a transcribed line', $citation->data->text[0]);
        self::assertNotNull($citation->data->date);
    }

    /**
     * A citation carries its cited event (the generated {@see SourceCitationEvent}): the event type
     * as its line value and the informant's ROLE.
     */
    #[Test]
    public function aCitationCarriesItsCitedEvent(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 EVEN BIRT\n4 ROLE CHIL\n"
            . "0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n";

        $document = $this->parse($gedcom);

        $citation = $document->individuals[0]->birt[0]->sour[0];

        self::assertNotNull($citation->even);
        self::assertSame('BIRT', $citation->even->value);
        self::assertSame('CHIL', $citation->even->role);
    }

    /**
     * A citation carries its inline note.
     */
    #[Test]
    public function aCitationCarriesItsInlineNote(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 BIRT\n2 SOUR @S1@\n3 NOTE a citation note\n"
            . "0 @S1@ SOUR\n1 TITL A source\n0 TRLR\n";

        $document = $this->parse($gedcom);

        $citation = $document->individuals[0]->birt[0]->sour[0];

        self::assertCount(1, $citation->note);
        self::assertSame('a citation note', $citation->note[0]->value);
    }

    /**
     * An inline (non-pointer) citation still yields a citation object, but carries no
     * cross-reference, so `->source()` returns NULL through the pointer guard rather than looking up
     * the index. (Its source-description text is not yet modelled and is currently dropped — a
     * documented deferral, exercised here only for the null-pointer resolution path.).
     */
    #[Test]
    public function anInlineCitationHasNoPointerAndResolvesToNull(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 BIRT\n2 SOUR A free-text citation\n0 TRLR\n";

        $document = $this->parse($gedcom);

        $citation = $document->individuals[0]->birt[0]->sour[0];

        self::assertNull($citation->xref);
        self::assertNull($citation->source($document));
    }

    /**
     * A citation whose pointer targets no record in the document resolves to NULL rather than
     * throwing.
     */
    #[Test]
    public function anUnresolvableCitationResolvesToNull(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @I1@ INDI\n1 BIRT\n2 SOUR @S9@\n0 TRLR\n";

        $document = $this->parse($gedcom);

        $citation = $document->individuals[0]->birt[0]->sour[0];

        self::assertSame('S9', $citation->xref);
        self::assertNull($citation->source($document));
    }

    /**
     * Parses the given GEDCOM string into the typed document.
     *
     * @param string $gedcom The GEDCOM source.
     *
     * @return GedcomDocument The parsed document.
     */
    private function parse(string $gedcom): GedcomDocument
    {
        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
