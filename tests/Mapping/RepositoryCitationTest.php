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
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\Substructure\Common\CallNumber;
use MagicSunday\Gedcom\Model\Substructure\Source\RepositoryCitation;
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
 * A repository citation (`REPO` under a source record) is a generated {@see RepositoryCitation}: it
 * carries the repository cross-reference and its call numbers, each a generated {@see CallNumber}
 * holding the shelf identifier and its media type. This proves the generator's container-reference
 * capability end to end — one generated container ({@see RepositoryCitation}) nests another
 * ({@see CallNumber}) — through the parser (#132).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(RepositoryCitation::class)]
#[CoversClass(CallNumber::class)]
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
#[UsesClass(SourceRecord::class)]
class RepositoryCitationTest extends TestCase
{
    /**
     * A source record's repository citation carries the repository cross-reference and its typed
     * call numbers — the nested generated container — with each call number's value and media type.
     */
    #[Test]
    public function aSourceRecordCarriesItsTypedRepositoryCitations(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @S1@ SOUR\n1 TITL A source\n1 REPO @R1@\n2 CALN Shelf 7\n3 MEDI Book\n"
            . "0 @R1@ REPO\n1 NAME A repository\n0 TRLR\n";

        $document = $this->parse($gedcom);

        self::assertCount(1, $document->sources);

        $repositories = $document->sources[0]->repo;
        self::assertCount(1, $repositories);

        $citation = $repositories[0];
        self::assertSame('R1', $citation->xref);

        self::assertCount(1, $citation->caln);

        $callNumber = $citation->caln[0];
        self::assertSame('Shelf 7', $callNumber->value);
        self::assertSame('Book', $callNumber->medi);
    }

    /**
     * An inline repository citation without call numbers still yields a typed citation carrying no
     * cross-reference rather than being dropped.
     */
    #[Test]
    public function anInlineRepositoryCitationYieldsATypedCitation(): void
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n"
            . "0 @S1@ SOUR\n1 TITL A source\n1 REPO\n2 CALN Shelf 9\n0 TRLR\n";

        $document = $this->parse($gedcom);

        $repositories = $document->sources[0]->repo;
        self::assertCount(1, $repositories);
        self::assertNull($repositories[0]->xref);
        self::assertCount(1, $repositories[0]->caln);
        self::assertSame('Shelf 9', $repositories[0]->caln[0]->value);
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
