<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Mapping;

use MagicSunday\Gedcom\Exception\MappingException;
use MagicSunday\Gedcom\Mapping\GedcomDocumentReader;
use MagicSunday\Gedcom\Mapping\GedcomObjectMapper;
use MagicSunday\Gedcom\Mapping\GedcomVersionDetector;
use MagicSunday\Gedcom\Mapping\JsonMapperFactory;
use MagicSunday\Gedcom\Mapping\RecordStream;
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\PersonalName;
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

use function array_map;
use function dirname;

/**
 * One record the model cannot build does not cost the rest of the file (#194).
 *
 * A record written without its cross-reference identifier is malformed — nothing can refer to it —
 * and it cannot be constructed. It used to abort the whole read, so every well-formed record in the
 * document was lost with it. It is now skipped, as an unrecognised record tag already was.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomDocumentReader::class)]
#[UsesClass(Parser::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(Reader::class)]
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
#[UsesClass(FamilyRecord::class)]
#[UsesClass(PersonalName::class)]
class RecordFailureContainmentTest extends TestCase
{
    /**
     * A record without its identifier is skipped, and every well-formed record around it — including
     * one of another kind, read after it — still maps.
     */
    #[Test]
    public function skipsAnIdentifierLessRecordAndKeepsTheRest(): void
    {
        $document = $this->parse(
            "0 INDI\n1 NAME /Doe/\n0 @I2@ INDI\n1 NAME /Roe/\n0 @F1@ FAM\n1 HUSB @I2@\n0 TRLR\n",
            '7.0'
        );

        self::assertSame(['I2'], array_map(
            static fn (IndividualRecord $i): string => $i->xref,
            $document->individuals
        ), 'The identifier-less record is skipped, its well-formed sibling is not.');

        self::assertCount(1, $document->families, 'A record read after the malformed one still maps.');
        self::assertSame('I2', $document->families[0]->husb);
    }

    /**
     * The containment holds under GEDCOM 5.5.1 too.
     */
    #[Test]
    public function skipsAnIdentifierLessRecordUnderGedcom551(): void
    {
        $document = $this->parse(
            "0 INDI\n1 NAME /Doe/\n0 @I2@ INDI\n1 NAME /Roe/\n0 TRLR\n",
            '5.5.1'
        );

        self::assertCount(1, $document->individuals);
        self::assertSame('I2', $document->individuals[0]->xref);
    }

    /**
     * The skip is gated on the malformation itself rather than on the mapping failing, so the mapper
     * keeps reporting a failure it cannot handle instead of it being swallowed into a quietly empty
     * document. Mapping a node that is no record at all still throws.
     */
    #[Test]
    public function stillFailsLoudlyWhenTheMapperCannotHandleTheNode(): void
    {
        $stream = (new StreamFactory())->createStream("0 @I1@ INDI\n1 NAME /Doe/\n0 TRLR\n");
        $stream->rewind();

        $record = (new GedcomTreeReader(new Reader($stream)))->readRecord();
        self::assertInstanceOf(GedcomNode::class, $record);

        $name = $record->firstChild('NAME');
        self::assertInstanceOf(GedcomNode::class, $name);

        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $this->expectException(MappingException::class);

        // A NAME is a substructure, not a top-level record — the mapper must say so, not stay quiet.
        (new GedcomObjectMapper($schema, JsonMapperFactory::create()))
            ->mapRecord($name, PersonalName::class);
    }

    /**
     * A document whose only record is malformed yields no records rather than throwing.
     */
    #[Test]
    public function yieldsNoRecordsWhenEveryRecordIsMalformed(): void
    {
        $document = $this->parse("0 INDI\n1 NAME /Doe/\n0 TRLR\n", '7.0');

        self::assertSame([], $document->individuals);
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
