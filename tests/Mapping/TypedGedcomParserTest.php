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
use MagicSunday\Gedcom\Mapping\GedcomObjectMapper;
use MagicSunday\Gedcom\Mapping\JsonMapperFactory;
use MagicSunday\Gedcom\Mapping\TypedGedcomParser;
use MagicSunday\Gedcom\Model\EventDetail;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\SubmitterRecord;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;
use function sys_get_temp_dir;
use function uniqid;

/**
 * Tests parsing a full GEDCOM stream into the typed model through the record class map.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(TypedGedcomParser::class)]
#[UsesClass(GedcomObjectMapper::class)]
#[UsesClass(JsonMapperFactory::class)]
#[UsesClass(SubmitterRecord::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(EventDetail::class)]
#[UsesClass(PlaceValue::class)]
#[UsesClass(GedcomDocument::class)]
class TypedGedcomParserTest extends TestCase
{
    /**
     * The level-0 records with a mapped class are parsed into their typed records in document
     * order, while the HEAD and TRLR transmission structures — which have no mapped class — are
     * skipped.
     */
    #[Test]
    public function parsesLevel0RecordsIntoTheModel(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 HEAD\n1 SOUR SomeApp\n"
            . "0 @SUBM1@ SUBM\n1 NAME John Doe\n"
            . "0 @I1@ INDI\n1 BIRT\n2 DATE 1 JAN 2000\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $parser = TypedGedcomParser::create(
            GedcomVersion::V551,
            [
                'SUBM' => SubmitterRecord::class,
                'INDI' => IndividualRecord::class,
            ]
        );

        $records = iterator_to_array($parser->parse($stream));

        self::assertCount(2, $records, 'HEAD and TRLR carry no mapped class and are skipped');

        $submitter = $records[0];
        self::assertInstanceOf(SubmitterRecord::class, $submitter);
        self::assertSame('SUBM1', $submitter->xref);
        self::assertSame('John Doe', $submitter->name);

        $individual = $records[1];
        self::assertInstanceOf(IndividualRecord::class, $individual);
        self::assertSame('I1', $individual->xref);
        self::assertCount(1, $individual->birt, 'the single BIRT event maps to one EventDetail');
        self::assertInstanceOf(DateValue::class, $individual->birt[0]->date, 'the BIRT DATE substructure is mapped');
    }

    /**
     * The streaming parser threads HEAD.PLAC.FORM the same way the aggregate reader does: a place
     * carrying no FORM of its own resolves its jurisdiction labels from the header-declared default,
     * so the fix reaches this parallel public entry point too.
     */
    #[Test]
    public function threadsTheHeaderPlaceFormOntoAFormlessPlace(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 PLAC\n2 FORM City, County, State, Country\n"
            . "0 @I1@ INDI\n1 BIRT\n2 PLAC Cove, Cache, Utah, USA\n"
            . "0 TRLR\n"
        );
        $stream->rewind();

        $parser  = TypedGedcomParser::create(GedcomVersion::V551, ['INDI' => IndividualRecord::class]);
        $records = iterator_to_array($parser->parse($stream));

        self::assertCount(1, $records);

        $individual = $records[0];
        self::assertInstanceOf(IndividualRecord::class, $individual);

        $place = $individual->birt[0]->plac;
        self::assertInstanceOf(PlaceValue::class, $place);
        self::assertSame(
            ['City' => 'Cove', 'County' => 'Cache', 'State' => 'Utah', 'Country' => 'USA'],
            $place->mapped(),
        );
    }

    /**
     * The eager parseDocument() surfaces the GEDCOM 7.0 header extension-tag schema (HEAD.SCHMA.TAG)
     * on the aggregate — the streaming parse() cannot, so this path must.
     */
    #[Test]
    public function parseDocumentSurfacesTheHeaderExtensionTagSchema(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 HEAD
            1 GEDC
            2 VERS 7.0
            1 SCHMA
            2 TAG _LOC https://example.com/loc
            0 @I1@ INDI
            0 TRLR
            GEDCOM);
        $stream->rewind();

        $parser   = TypedGedcomParser::create(GedcomVersion::V70, ['INDI' => IndividualRecord::class]);
        $document = $parser->parseDocument($stream);

        self::assertSame(['_LOC' => ['https://example.com/loc']], $document->extensionTags);
        self::assertCount(1, $document->individuals);
    }

    /**
     * A stream with only unmapped records yields nothing rather than failing.
     */
    #[Test]
    public function skipsRecordsWithNoMappedClass(): void
    {
        $stream = (new StreamFactory())->createStream("0 HEAD\n1 SOUR SomeApp\n0 TRLR\n");
        $stream->rewind();

        $parser = TypedGedcomParser::create(GedcomVersion::V551, ['INDI' => IndividualRecord::class]);

        self::assertSame([], iterator_to_array($parser->parse($stream)));
    }

    /**
     * Creating a parser against a registry path with no structures fails loud rather than
     * yielding a parser that silently maps nothing.
     */
    #[Test]
    public function failsLoudlyWhenTheRegistryCannotBeLoaded(): void
    {
        $this->expectException(MappingException::class);

        TypedGedcomParser::create(
            GedcomVersion::V551,
            ['INDI' => IndividualRecord::class],
            sys_get_temp_dir() . '/gedcom-missing-registry-' . uniqid(),
        );
    }
}
