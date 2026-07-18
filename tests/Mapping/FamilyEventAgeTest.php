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
use MagicSunday\Gedcom\Model\AttributeDetail;
use MagicSunday\Gedcom\Model\EventDetail;
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\Substructure\Common\SpouseAge;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\StreamFactory;
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_map;
use function file_get_contents;

/**
 * A family's events and attributes now type the ages of the two partners at them (`HUSB`.`AGE` and
 * `WIFE`.`AGE`) as typed {@see SpouseAge} objects rather than leaving them on `$unknown`
 * (#132, #166).
 *
 * These are the substructures a family's events and attributes have beyond an individual's: each is
 * a container whose sole purpose is to carry one age, so the age is exposed as the typed
 * {@see AgeValue} the rest of the model uses. The container has the same shape in both GEDCOM
 * versions, though GEDCOM 7.0 permits it on more structures.
 *
 * The tag is deliberately overloaded in GEDCOM: at the record level `HUSB` is the family's pointer to
 * the husband, while beneath an event or attribute it is this age container. Both forms are asserted
 * together so neither can start consuming the other — under GEDCOM 5.5.1, since the record-level
 * pointer does not currently map under GEDCOM 7.0 at all (see issue #185).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(SpouseAge::class)]
#[CoversClass(EventDetail::class)]
#[CoversClass(AttributeDetail::class)]
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
#[UsesClass(FamilyRecord::class)]
#[UsesClass(IndividualRecord::class)]
#[UsesClass(AgeValue::class)]
#[UsesClass(RawSubstructure::class)]
class FamilyEventAgeTest extends TestCase
{
    /**
     * A marriage types both partners' ages at it as parsed durations.
     */
    #[Test]
    public function typesBothSpouseAgesOfAMarriage(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 MARR\n2 DATE 1900\n2 HUSB\n3 AGE 30y\n2 WIFE\n3 AGE 28y\n0 TRLR\n",
            '7.0'
        )->families[0];

        $marriage = $family->marr[0];

        self::assertInstanceOf(SpouseAge::class, $marriage->husb);
        self::assertSame(30, $marriage->husb->age?->years, 'The container exposes a parsed age, not raw text.');
        self::assertInstanceOf(SpouseAge::class, $marriage->wife);
        self::assertSame(28, $marriage->wife->age?->years);
        self::assertSame([], $this->tags($marriage->unknown));
    }

    /**
     * The ages type under GEDCOM 5.5.1 too, where the same containers apply.
     */
    #[Test]
    public function typesTheSpouseAgesUnderGedcom551(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 DIV\n2 HUSB\n3 AGE 45y\n2 WIFE\n3 AGE 43y\n0 TRLR\n",
            '5.5.1'
        )->families[0];

        $divorce = $family->div[0];

        self::assertSame(45, $divorce->husb?->age?->years);
        self::assertSame(43, $divorce->wife?->age?->years);
        self::assertSame([], $this->tags($divorce->unknown));
    }

    /**
     * A family attribute carries the containers as well — the schema permits them on the residence
     * in both versions, not only on events.
     */
    #[Test]
    public function typesTheSpouseAgesOfAFamilyAttribute(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 HUSB @I1@\n1 WIFE @I2@\n1 RESI\n2 HUSB\n3 AGE 40y\n2 WIFE\n3 AGE 38y\n0 TRLR\n",
            '5.5.1'
        )->families[0];

        // Both tags are overloaded: at the record level they are the family's pointers to the two
        // partners, beneath the attribute they are the age containers. Neither may consume the other,
        // and the collision only manifests when both forms appear in one parse.
        self::assertSame('I1', $family->husb, "The family's own HUSB stays a pointer.");
        self::assertSame('I2', $family->wife, "The family's own WIFE stays a pointer.");

        $residence = $family->resi[0];

        self::assertInstanceOf(SpouseAge::class, $residence->husb);
        self::assertSame(40, $residence->husb->age?->years);
        self::assertSame(38, $residence->wife?->age?->years);
        self::assertSame([], $this->tags($residence->unknown));
    }

    /**
     * GEDCOM 7.0 additionally permits them on the child-count and generic-fact attributes.
     */
    #[Test]
    public function typesTheSpouseAgesOfTheGedcom7FamilyAttributes(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 NCHI 3\n2 HUSB\n3 AGE 35y\n1 FACT Endogamous\n2 TYPE Relation\n"
            . "2 WIFE\n3 AGE 33y\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertSame(35, $family->nchi[0]->husb?->age?->years);
        self::assertSame(33, $family->fact[0]->wife?->age?->years);
        self::assertSame([], $this->tags($family->nchi[0]->unknown));
        self::assertSame([], $this->tags($family->fact[0]->unknown));
    }

    /**
     * Only the partner the file names carries an age; the other stays unset rather than defaulting.
     */
    #[Test]
    public function leavesTheAbsentSpouseAgeUnset(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 MARR\n2 HUSB\n3 AGE 30y\n0 TRLR\n",
            '7.0'
        )->families[0];

        self::assertSame(30, $family->marr[0]->husb?->age?->years);
        self::assertNull($family->marr[0]->wife);
    }

    /**
     * The schema requires the age beneath the container, but a file that omits it yields the
     * container with no age rather than losing it.
     */
    #[Test]
    public function toleratesAContainerWithoutAnAge(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 MARR\n2 HUSB\n0 TRLR\n",
            '7.0'
        )->families[0];

        $husband = $family->marr[0]->husb;

        self::assertInstanceOf(SpouseAge::class, $husband);
        self::assertNull($husband->age, 'A required age that is absent is tolerated, not rejected.');
    }

    /**
     * The containers belong to a family's events and attributes: on an individual's event the schema
     * does not permit them, so they stay on that event's own `$unknown` rather than typing.
     */
    #[Test]
    public function doesNotTypeTheContainersOnAnIndividualEvent(): void
    {
        $individual = $this->parse(
            "0 @I1@ INDI\n1 BIRT\n2 HUSB\n3 AGE 30y\n0 TRLR\n",
            '7.0'
        )->individuals[0];

        self::assertNull($individual->birt[0]->husb);
        self::assertSame(['HUSB'], $this->tags($individual->birt[0]->unknown));
    }

    /**
     * An out-of-schema substructure beneath the container is preserved verbatim rather than dropped.
     */
    #[Test]
    public function preservesAnUnknownSubstructureBeneathTheContainer(): void
    {
        $family = $this->parse(
            "0 @F1@ FAM\n1 MARR\n2 HUSB\n3 AGE 30y\n3 _CUSTOM Extension payload\n0 TRLR\n",
            '7.0'
        )->families[0];

        $husband = $family->marr[0]->husb;

        self::assertInstanceOf(SpouseAge::class, $husband);
        self::assertSame(30, $husband->age?->years);
        self::assertSame(['_CUSTOM'], $this->tags($husband->unknown));
        self::assertSame('Extension payload', $husband->unknown[0]->value);
    }

    /**
     * The ages type out of the shipped conformance corpus, whose annulment carries a multi-unit
     * duration the synthetic whole-year fixtures do not exercise.
     */
    #[Test]
    public function typesTheSpouseAgesOfTheConformanceCorpus(): void
    {
        $path = __DIR__ . '/../files/allged.ged';
        self::assertFileExists($path);

        $stream = (new StreamFactory())->createStream((string) file_get_contents($path));
        $stream->rewind();

        $annulment = (new Parser($stream))->parse()->families[0]->anul[0];

        self::assertInstanceOf(SpouseAge::class, $annulment->husb);
        self::assertSame(42, $annulment->husb->age?->years);

        self::assertInstanceOf(SpouseAge::class, $annulment->wife);
        $wifeAge = $annulment->wife->age;
        self::assertInstanceOf(AgeValue::class, $wifeAge);
        self::assertSame(42, $wifeAge->years);
        self::assertSame(6, $wifeAge->months, 'The corpus wife age is a multi-unit duration.');
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
    private function parse(string $body, string $version): GedcomDocument
    {
        $charset = $version === '5.5.1' ? "1 CHAR ASCII\n" : '';
        $gedcom  = "0 HEAD\n1 GEDC\n2 VERS " . $version . "\n" . $charset . $body;

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return (new Parser($stream))->parse();
    }
}
