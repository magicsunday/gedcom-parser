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
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SubmitterRecord;
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
 * The submitter and repository records now type the remainder of their contact block — the submitter's
 * email addresses (`EMAIL`), fax numbers (`FAX`) and web pages (`WWW`), and the repository's web pages
 * (`WWW`) — rather than leaving them on `$unknown` (#132, #168). The structured address (`ADDR`) is
 * typed as well; see {@see StructuredAddressTest}.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(SubmitterRecord::class)]
#[CoversClass(RepositoryRecord::class)]
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
class RecordContactTest extends TestCase
{
    /**
     * A submitter record types its email addresses, fax numbers and web pages alongside its phones.
     */
    #[Test]
    public function typesTheSubmitterContactBlock(): void
    {
        $document = $this->parse(
            "0 @U1@ SUBM\n1 NAME A submitter\n1 PHON +1 555 0001\n1 EMAIL a@example.test\n"
            . "1 FAX +1 555 0002\n1 WWW https://example.test\n0 TRLR\n",
            '7.0'
        );

        $submitter = $document->submitters[0];
        self::assertSame(['+1 555 0001'], $submitter->phon);
        self::assertSame(['a@example.test'], $submitter->email);
        self::assertSame(['+1 555 0002'], $submitter->fax);
        self::assertSame(['https://example.test'], $submitter->www);
        self::assertSame([], $this->tags($submitter->unknown));
    }

    /**
     * The contact block types under GEDCOM 5.5.1 too, where a submitter equally carries email and
     * web-page entries.
     */
    #[Test]
    public function typesA551SubmitterContactBlock(): void
    {
        $document = $this->parse(
            "0 @U1@ SUBM\n1 NAME A submitter\n1 EMAIL legacy@example.test\n1 WWW https://legacy.example.test\n0 TRLR\n",
            '5.5.1'
        );

        $submitter = $document->submitters[0];
        self::assertSame(['legacy@example.test'], $submitter->email);
        self::assertSame(['https://legacy.example.test'], $submitter->www);
        self::assertSame([], $this->tags($submitter->unknown));
    }

    /**
     * A repository record types its web pages alongside the contact fields it already modelled.
     */
    #[Test]
    public function typesTheRepositoryWebPages(): void
    {
        $document = $this->parse(
            "0 @R1@ REPO\n1 NAME A repository\n1 WWW https://repo.example.test\n0 TRLR\n",
            '7.0'
        );

        $repository = $document->repositories[0];
        self::assertSame(['https://repo.example.test'], $repository->www);
        self::assertSame([], $this->tags($repository->unknown));
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
