<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Model\Gedcom;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\TestCase;

use function basename;
use function glob;

/**
 * Tests the high-level parser against a representative GEDCOM record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 *
 * @covers \MagicSunday\Gedcom\Parser
 */
class ParserTest extends TestCase
{
    /**
     * Parses an individual record and exposes its identifier, sex and personal name.
     *
     * @test
     */
    public function parsePartial(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 @X116@ INDI
            1 SEX M
            1 BIRT
            2 DATE 01 JAN 1950
            1 DEAT
            2 DATE 01 JAN 2000
            1 FAMS @X118@
            1 NAME Lt. Cmndr. Max Joachim /der Edle/ von Musterhausen
            2 TYPE BIRTH
            2 GIVN Max Joachim
            2 SPFX der
            2 SURN Edle
            2 NPFX Lt. Cmndr.
            2 NSFX von Musterhausen
            GEDCOM);

        $stream->rewind();

        $gedcom = (new Parser($stream))->parse();

        $individuals = $gedcom->getIndividual();

        self::assertCount(1, $individuals);

        $individual = $individuals[0];

        self::assertInstanceOf(IndividualRecord::class, $individual);
        self::assertSame('X116', $individual->getXref());
        self::assertSame('M', $individual->getSex());
        self::assertSame(
            'Lt. Cmndr. Max Joachim /der Edle/ von Musterhausen',
            $individual->getNames()[0]->getName()
        );
    }

    /**
     * A child-sealing (SLGC) ordinance parses without a fatal error. Before GH-33,
     * loading the SealingChild model linked an invalid covariant return-type override on
     * SealingChildInterface::getDateStatus(), an uncatchable class-load fatal; this pins
     * that regression independently of the bundled fixtures.
     *
     * @test
     */
    public function parsesSealingChildOrdinanceWithoutCovarianceFatal(): void
    {
        $stream = (new StreamFactory())->createStream(<<<GEDCOM
            0 @I1@ INDI
            1 SLGC
            2 STAT BIC
            2 DATE 01 JAN 1970
            2 FAMC @F1@
            GEDCOM);

        $stream->rewind();

        $gedcom = (new Parser($stream))->parse();

        self::assertCount(1, $gedcom->getIndividual());
    }

    /**
     * The given name, surname and suffix are derived from the NAME slash convention when
     * no explicit sub-tags are present, and the display name drops the surname slashes.
     *
     * @dataProvider nameFormProvider
     *
     * @test
     *
     * @param string      $nameLine The GEDCOM NAME line under test.
     * @param string|null $given    The expected given name.
     * @param string|null $surname  The expected surname.
     * @param string|null $suffix   The expected name suffix.
     * @param string|null $display  The expected display name.
     */
    public function derivesNamePartsFromSlashConvention(
        string $nameLine,
        ?string $given,
        ?string $surname,
        ?string $suffix,
        ?string $display
    ): void {
        $stream = (new StreamFactory())->createStream("0 @I1@ INDI\n" . $nameLine . "\n");
        $stream->rewind();

        $name = (new Parser($stream))->parse()->getIndividual()[0]->getNames()[0];

        self::assertSame($given, $name->getGivenName());
        self::assertSame($surname, $name->getSurname());
        self::assertSame($suffix, $name->getNameSuffix());
        self::assertSame($display, $name->getDisplayName());
    }

    /**
     * One row per nameParts() branch: full form / missing trailing slash / no slash /
     * surname only / empty name.
     *
     * @return array<string, array{0: string, 1: string|null, 2: string|null, 3: string|null, 4: string|null}>
     */
    public static function nameFormProvider(): array
    {
        return [
            'full form'          => ['1 NAME John /Smith/ Jr', 'John', 'Smith', 'Jr', 'John Smith Jr'],
            'missing trailing /' => ['1 NAME John /Smith', 'John', 'Smith', null, 'John Smith'],
            'no slash'           => ['1 NAME John', 'John', null, null, 'John'],
            'surname only'       => ['1 NAME /Smith/', null, 'Smith', null, 'Smith'],
            'empty name'         => ['1 NAME', null, null, null, null],
        ];
    }

    /**
     * Explicit GIVN/SURN/NSFX sub-tags take precedence over the slash-derived name parts.
     *
     * @test
     */
    public function explicitNamePartsWinOverSlashDerivation(): void
    {
        $stream = (new StreamFactory())->createStream(
            "0 @I1@ INDI\n1 NAME John /Smith/ Jr\n2 GIVN Johnny\n2 SURN Smithson\n2 NSFX Sr\n"
        );
        $stream->rewind();

        $name = (new Parser($stream))->parse()->getIndividual()[0]->getNames()[0];

        self::assertSame('Johnny', $name->getGivenName());
        self::assertSame('Smithson', $name->getSurname());
        self::assertSame('Sr', $name->getNameSuffix());
    }

    /**
     * Provides every bundled GEDCOM fixture.
     *
     * @return array<string, array{0: string}>
     */
    public static function fixtureProvider(): array
    {
        $cases = [];

        foreach (glob(__DIR__ . '/files/*.ged') as $file) {
            $cases[basename($file)] = [$file];
        }

        return $cases;
    }

    /**
     * Every bundled fixture parses into a Gedcom document without raising an exception.
     *
     * @dataProvider fixtureProvider
     *
     * @test
     *
     * @param string $file The absolute path to the GEDCOM fixture.
     */
    public function parsesFixtureWithoutError(string $file): void
    {
        $stream = (new StreamFactory())->createStreamFromFile($file);

        self::assertInstanceOf(Gedcom::class, (new Parser($stream))->parse());
    }
}
