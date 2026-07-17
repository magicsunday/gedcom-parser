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
use MagicSunday\Gedcom\Model\MultimediaRecord;
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SourceRecord;
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
 * The GEDCOM 7.0 shared-note pointer (`SNOTE`) reaches beyond the individual and family records: the
 * submitter, source and repository records carry it, and the multimedia record carries both `SNOTE`
 * and the restriction notice (`RESN`). These are now typed rather than left on `$unknown` (#132, #167).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(SubmitterRecord::class)]
#[CoversClass(SourceRecord::class)]
#[CoversClass(RepositoryRecord::class)]
#[CoversClass(MultimediaRecord::class)]
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
class RecordSharedNoteBreadthTest extends TestCase
{
    /**
     * The submitter, source and repository records type their shared-note pointers; each stays off
     * `$unknown`.
     */
    #[Test]
    public function typesSharedNotesOnTheLeanRecords(): void
    {
        $document = $this->parse(
            "0 @U1@ SUBM\n1 NAME A submitter\n1 SNOTE @N1@\n"
            . "0 @S1@ SOUR\n1 TITL A source\n1 SNOTE @N1@\n"
            . "0 @R1@ REPO\n1 NAME A repository\n1 SNOTE @N1@\n"
            . "0 @N1@ SNOTE A shared note\n0 TRLR\n",
            '7.0'
        );

        self::assertSame(['N1'], $document->submitters[0]->snote);
        self::assertSame([], $this->tags($document->submitters[0]->unknown));
        self::assertSame(['N1'], $document->sources[0]->snote);
        self::assertSame([], $this->tags($document->sources[0]->unknown));
        self::assertSame(['N1'], $document->repositories[0]->snote);
        self::assertSame([], $this->tags($document->repositories[0]->unknown));
    }

    /**
     * The multimedia record types both its shared-note pointer and its restriction notice.
     */
    #[Test]
    public function typesTheMultimediaRestrictionAndSharedNote(): void
    {
        $document = $this->parse(
            "0 @M1@ OBJE\n1 FILE photo.jpg\n2 FORM image/jpeg\n1 RESN confidential\n1 SNOTE @N1@\n"
            . "0 @N1@ SNOTE A shared note\n0 TRLR\n",
            '7.0'
        );

        self::assertSame('confidential', $document->multimedia[0]->resn);
        self::assertSame(['N1'], $document->multimedia[0]->snote);
        self::assertSame([], $this->tags($document->multimedia[0]->unknown));
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
