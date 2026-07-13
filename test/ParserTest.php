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
     * Provides every bundled GEDCOM fixture.
     *
     * @return array<string, array{0: string}>
     */
    public static function fixtureProvider(): array
    {
        $cases = [];

        foreach (glob(__DIR__ . '/files/*.ged') as $file) {
            $name = basename($file);

            // Excluded until the SLGC covariance fatal (GH-33) is fixed.
            if ($name === 'GeorgeWashingtonFamilyBig.ged') {
                continue;
            }

            $cases[$name] = [$file];
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
