<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Exception\UnsupportedEncodingException;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Stream;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\TestCase;

use function implode;
use function mb_check_encoding;
use function mb_convert_encoding;

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
     * A document declaring CHAR ASCII (simple.ged) parses, and a plain-ASCII value passes
     * through byte-identical.
     *
     * @test
     */
    public function parsesAsciiPassThrough(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        $found = false;

        while ($reader->read()) {
            if (($reader->level() === 0) && ($reader->tag() === 'INDI')) {
                $found = true;
            }

            // The ASCII CHAR value passes through byte-identical.
            if ($reader->tag() === 'CHAR') {
                self::assertSame('ASCII', $reader->value());
            }
        }

        self::assertTrue($found);
    }

    /**
     * A UTF-16 document (little- and big-endian, with a byte-order mark) is transcoded to
     * UTF-8 and its non-ASCII value round-trips.
     *
     * @dataProvider utf16Provider
     *
     * @test
     *
     * @param string $bom      the byte-order mark
     * @param string $encoding the mb source encoding
     */
    public function parsesUtf16WithBom(string $bom, string $encoding): void
    {
        $utf8  = "0 HEAD\n1 CHAR UNICODE\n0 @I1@ INDI\n1 NAME René Ångström\n0 TRLR\n";
        $bytes = $bom . (string) mb_convert_encoding($utf8, $encoding, 'UTF-8');

        self::assertSame('René Ångström', $this->firstNameValue($this->rewoundStream($bytes)));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function utf16Provider(): array
    {
        return [
            'little-endian' => ["\xFF\xFE", 'UTF-16LE'],
            'big-endian'    => ["\xFE\xFF", 'UTF-16BE'],
        ];
    }

    /**
     * A UTF-16 document without a byte-order mark is detected by the null-interleaving
     * heuristic — for both endiannesses — and parsed correctly.
     *
     * @dataProvider utf16EncodingProvider
     *
     * @test
     *
     * @param string $encoding the mb source encoding
     */
    public function parsesUtf16WithoutBomViaNullHeuristic(string $encoding): void
    {
        $utf8  = "0 HEAD\n0 @I1@ INDI\n1 NAME Zoë\n0 TRLR\n";
        $bytes = (string) mb_convert_encoding($utf8, $encoding, 'UTF-8');

        self::assertSame('Zoë', $this->firstNameValue($this->rewoundStream($bytes)));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function utf16EncodingProvider(): array
    {
        return [
            'little-endian' => ['UTF-16LE'],
            'big-endian'    => ['UTF-16BE'],
        ];
    }

    /**
     * A UTF-16 stream truncated mid-code-unit (an odd trailing byte held back by the carry)
     * still parses the complete records and flushes the leftover best-effort at end of stream.
     *
     * @test
     */
    public function flushesTruncatedUtf16TailAtEndOfStream(): void
    {
        $utf8  = "0 @I1@ INDI\n1 NAME Zoë\n0 TRLR\n";
        $bytes = "\xFF\xFE" . (string) mb_convert_encoding($utf8, 'UTF-16LE', 'UTF-8') . "\x41";

        $individuals = (new Parser($this->rewoundStream($bytes)))->parse()->getIndividual();

        self::assertCount(1, $individuals);
    }

    /**
     * A single-byte stream with NO CHAR declaration defaults to ANSEL (the 5.5.1 default), so
     * a high byte decodes as ANSEL rather than passing through as raw bytes.
     *
     * @test
     */
    public function defaultsToAnselWhenCharIsAbsent(): void
    {
        // No HEAD.CHAR anywhere; the 0xCF byte is ANSEL "es zet".
        $stream = $this->rewoundStream("0 @I1@ INDI\n1 NAME \xCF\n0 TRLR\n");

        self::assertSame('ß', $this->firstNameValue($stream));
    }

    /**
     * Documented known limitation: an ANSEL combining mark split from its base across a
     * CONC continuation does not compose, because the reader decodes per physical line while
     * CONC/CONT concatenation happens later. The correct fix (decode after assembly) is
     * deferred to the typed-value work (#25/#20).
     *
     * @test
     */
    public function diacriticSplitAcrossConcIsAKnownLimitation(): void
    {
        // ANSEL acute (0xE2) ends the NAME line; its base 'e' is on the CONC continuation.
        $stream = $this->rewoundStream("0 @I1@ INDI\n1 NAME Ren\xE2\n2 CONC e\n0 TRLR\n");
        $reader = new Reader($stream);

        $values = [];

        while ($reader->read()) {
            if (($reader->tag() === 'NAME') || ($reader->tag() === 'CONC')) {
                $values[] = (string) $reader->value();
            }
        }

        // The mark and its base are decoded on separate lines, so they never compose to "é".
        self::assertStringNotContainsString('é', implode('', $values));
    }

    /**
     * An astral character (a surrogate pair) whose halves fall on either side of a read-chunk
     * boundary must not be corrupted — the reader holds a lone high surrogate back until its
     * low half arrives. Reading one byte at a time forces the split.
     *
     * @test
     */
    public function decodesAstralCharacterSplitAcrossChunkBoundary(): void
    {
        $utf8  = "0 @I1@ INDI\n1 NAME \u{1F600}X\n0 TRLR\n";
        $bytes = "\xFF\xFE" . (string) mb_convert_encoding($utf8, 'UTF-16LE', 'UTF-8');

        $name = $this->firstNameValue($this->oneByteStream($bytes));

        self::assertStringContainsString("\u{1F600}", $name ?? '');
    }

    /**
     * A byte-framed stream that declares CHAR UNICODE but has no BOM is undetectable as
     * UTF-16 and is rejected rather than silently mis-parsed.
     *
     * @test
     */
    public function rejectsBomlessUnicodeDeclaration(): void
    {
        $stream = $this->rewoundStream("0 HEAD\n1 CHAR UNICODE\n0 TRLR\n");
        $reader = new Reader($stream);

        $this->expectException(UnsupportedEncodingException::class);

        while ($reader->read()) {
            // The CHAR UNICODE sniff must throw before parsing completes.
        }
    }

    /**
     * Returns the value of the first NAME line parsed from the given stream.
     *
     * @param Stream $stream a readable stream over a GEDCOM document
     *
     * @return string|null the first NAME value, or NULL when none is present
     */
    private function firstNameValue(Stream $stream): ?string
    {
        $reader = new Reader($stream);

        while ($reader->read()) {
            if ($reader->tag() === 'NAME') {
                return $reader->value();
            }
        }

        return null;
    }

    /**
     * Wraps the given raw bytes in a rewound in-memory stream.
     *
     * @param string $bytes the raw document bytes
     *
     * @return Stream a rewound stream over the bytes
     */
    private function rewoundStream(string $bytes): Stream
    {
        $stream = (new StreamFactory())->createStream($bytes);
        $stream->rewind();

        return $stream;
    }

    /**
     * Wraps the given raw bytes in a stream whose read() yields at most one byte per call.
     *
     * @param string $bytes the raw document bytes
     *
     * @return Stream a rewound single-byte-per-read stream
     */
    private function oneByteStream(string $bytes): Stream
    {
        return new class($bytes) extends Stream {
            public function __construct(string $bytes)
            {
                parent::__construct('php://temp', 'r+');

                $this->write($bytes);
                $this->rewind();
            }

            public function read(int $length): string
            {
                return parent::read(1);
            }
        };
    }
}
