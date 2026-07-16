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
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\MapCoordinates;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * A value-object leaf (`DATE`/`PLAC`/`AGE`) now carries its own `$unknown` list, so an out-of-schema
 * tag nested directly under it — which the leaf's grammar does not consume — is preserved as a
 * {@see RawSubstructure} rather than dropped. This narrows the last preservation boundary ("point 3")
 * for the value-object leaves (#143); a scalar leaf such as `SEX` still cannot carry one.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(DateValue::class)]
#[CoversClass(PlaceValue::class)]
#[CoversClass(AgeValue::class)]
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
#[UsesClass(EventDetail::class)]
#[UsesClass(MapCoordinates::class)]
#[UsesClass(RawSubstructure::class)]
class LeafValueUnknownPreservationTest extends TestCase
{
    /**
     * An out-of-schema tag directly under a `DATE` is preserved on the DateValue's `$unknown`, and
     * the date itself still parses. (GEDCOM 7.0, where a DATE declares substructures and is therefore
     * shaped as a structured object that can carry the extension.).
     */
    #[Test]
    public function preservesAnExtensionUnderADateValue(): void
    {
        $date = $this->parse("1 BIRT\n2 DATE 1 JAN 1900\n3 _CUSTOM x\n", '7.0')->birt[0]->date;

        self::assertNotNull($date);
        self::assertSame('1 JAN 1900', $date->raw);
        self::assertSame('x', $this->byTag($date->unknown)['_CUSTOM']->value ?? null);
    }

    /**
     * An out-of-schema tag under a `PLAC` is preserved on the PlaceValue's `$unknown`, alongside the
     * handler-parsed place name (a PLAC always declares substructures, so this holds in 5.5.1 too).
     */
    #[Test]
    public function preservesAnExtensionUnderAPlaceValue(): void
    {
        $place = $this->parse("1 BIRT\n2 PLAC Berlin\n3 _CUSTOM y\n")->birt[0]->plac;

        self::assertNotNull($place);
        self::assertSame('Berlin', $place->raw);
        self::assertSame('y', $this->byTag($place->unknown)['_CUSTOM']->value ?? null);
    }

    /**
     * An out-of-schema tag under an `AGE` is preserved on the AgeValue's `$unknown` (GEDCOM 7.0).
     */
    #[Test]
    public function preservesAnExtensionUnderAnAgeValue(): void
    {
        $age = $this->parse("1 BIRT\n2 AGE 30y\n3 _CUSTOM z\n", '7.0')->birt[0]->age;

        self::assertNotNull($age);
        self::assertSame('30y', $age->raw);
        self::assertSame('z', $this->byTag($age->unknown)['_CUSTOM']->value ?? null);
    }

    /**
     * A GEDCOM 5.5.1 `DATE` declares no substructures, so it would ordinarily be shaped as a bare
     * string; but when it actually carries an out-of-schema child, it is shaped as a structured
     * object so the extension is preserved on the DateValue's `$unknown` — the date still parses.
     */
    #[Test]
    public function preservesAnExtensionUnderAStructurelessLeafThatCarriesChildren(): void
    {
        $date = $this->parse("1 BIRT\n2 DATE 1 JAN 1900\n3 _CUSTOM x\n")->birt[0]->date;

        self::assertNotNull($date);
        self::assertSame('1 JAN 1900', $date->raw);
        self::assertSame('x', $this->byTag($date->unknown)['_CUSTOM']->value ?? null);
    }

    /**
     * A recognised substructure the leaf's handler consumes (a `PLAC`'s `MAP`) is NOT routed into
     * `$unknown`: the coordinates are parsed and `$unknown` stays empty. Only out-of-schema tags land
     * on the value object's `$unknown`.
     */
    #[Test]
    public function doesNotRouteAConsumedSubstructureIntoUnknown(): void
    {
        $place = $this->parse("1 BIRT\n2 PLAC Berlin\n3 MAP\n4 LATI N52.5\n4 LONG E13.4\n")->birt[0]->plac;

        self::assertNotNull($place);
        self::assertNotNull($place->coordinates);
        self::assertSame([], $place->unknown);
    }

    /**
     * Indexes preserved substructures by their tag for assertion.
     *
     * @param list<RawSubstructure> $unknown The preserved substructures.
     *
     * @return array<string, RawSubstructure> The substructures keyed by tag.
     */
    private function byTag(array $unknown): array
    {
        $byTag = [];

        foreach ($unknown as $substructure) {
            $byTag[$substructure->tag] = $substructure;
        }

        return $byTag;
    }

    /**
     * Parses the given individual body into the first individual record.
     *
     * @param string $body    The GEDCOM lines under the individual record.
     * @param string $version The GEDCOM version to declare in the header.
     *
     * @return IndividualRecord The parsed individual.
     */
    private function parse(string $body, string $version = '5.5.1'): IndividualRecord
    {
        $charset = $version === '5.5.1' ? "1 CHAR ASCII\n" : '';
        $gedcom  = "0 HEAD\n1 GEDC\n2 VERS " . $version . "\n" . $charset . "0 @I1@ INDI\n" . $body . "0 TRLR\n";

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse()->individuals[0];
    }
}
