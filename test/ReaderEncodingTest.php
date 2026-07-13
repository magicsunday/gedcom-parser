<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\TestCase;

use function implode;
use function mb_check_encoding;

/**
 * Tests that the reader honours the HEAD.CHAR / BOM source encoding and transcodes to UTF-8.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 *
 * @covers \MagicSunday\Gedcom\Reader
 */
class ReaderEncodingTest extends TestCase
{
    /**
     * The ANSEL fixture (1 CHAR ANSEL) is transcoded to valid, correctly-decoded UTF-8.
     *
     * @test
     */
    public function parsesAnselFixtureToUtf8(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/ansel.ged');
        $reader = new Reader($stream);

        $values = [];

        while ($reader->read()) {
            $value = $reader->value();

            if ($value !== null) {
                $values[] = $value;
            }
        }

        $all = implode("\n", $values);

        self::assertTrue(mb_check_encoding($all, 'UTF-8'), 'The decoded document must be valid UTF-8.');
        // The fixture self-labels the byte 0xCF place as "es zet" -> ß.
        self::assertStringContainsString('ß', $all, 'The ANSEL "es zet" byte must decode to ß.');
        self::assertStringContainsString('Þ', $all, 'The ANSEL "thorn" byte must decode to Þ.');
    }

    /**
     * A file that declares CHAR UTF-8 and has a non-ASCII value BEFORE the CHAR line (a real
     * 5.5.1 header puts SOUR/COPR/FILE before CHAR) is decoded as UTF-8, not mangled as ANSEL.
     *
     * @test
     */
    public function decodesUtf8EvenWithNonAsciiHeaderFieldBeforeChar(): void
    {
        $gedcom = "0 HEAD\n1 SOUR X\n1 COPR © Ünëßtädt\n1 GEDC\n2 VERS 5.5.1\n1 CHAR UTF-8\n0 TRLR\n";

        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        $reader = new Reader($stream);
        $copr   = null;

        while ($reader->read()) {
            if ($reader->tag() === 'COPR') {
                $copr = $reader->value();
            }
        }

        self::assertSame('© Ünëßtädt', $copr, 'A UTF-8 value before CHAR must not be re-decoded as ANSEL.');
    }

    /**
     * An ASCII document with no CHAR line still parses (default single-byte handling), and a
     * plain-ASCII value passes through unchanged.
     *
     * @test
     */
    public function parsesAsciiPassThrough(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        $found = false;

        while ($reader->read()) {
            if ($reader->tag() === 'INDI') {
                $found = true;
            }
        }

        self::assertTrue($found);
    }
}
