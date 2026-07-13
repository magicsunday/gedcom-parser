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
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Stream;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function implode;
use function mb_check_encoding;
use function mb_convert_encoding;
use function trim;

/**
 * Tests that the reader honours the HEAD.CHAR / BOM source encoding and transcodes to UTF-8.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Reader::class)]
class ReaderEncodingTest extends TestCase
{
    /**
     * The ANSEL fixture (1 CHAR ANSEL) is transcoded to valid, correctly-decoded UTF-8.
     */
    #[Test]
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
     */
    #[Test]
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
     */
    #[Test]
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
     * @param string $bom      the byte-order mark
     * @param string $encoding the mb source encoding
     */
    #[DataProvider('utf16Provider')]
    #[Test]
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
     * @param string $encoding the mb source encoding
     */
    #[DataProvider('utf16EncodingProvider')]
    #[Test]
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
     * does not break parsing: the complete records are read and the incomplete tail is
     * silently discarded rather than emitted as a replacement character.
     */
    #[Test]
    public function discardsIncompleteUtf16TailAtEndOfStream(): void
    {
        $utf8  = "0 @I1@ INDI\n1 NAME Zoë\n0 TRLR\n";
        $bytes = "\xFF\xFE" . (string) mb_convert_encoding($utf8, 'UTF-16LE', 'UTF-8') . "\x41";

        $reader = new Reader($this->rewoundStream($bytes));
        $lines  = [];

        // Reading all the way to EOF must not surface a stray line from the truncated tail
        // (a best-effort flush would emit a "?" line and throw UnableToParseLineException).
        while ($reader->read()) {
            $lines[] = trim($reader->current());
        }

        self::assertSame(['0 @I1@ INDI', '1 NAME Zoë', '0 TRLR'], $lines);
    }

    /**
     * A single-byte stream with NO CHAR declaration defaults to ANSEL (the 5.5.1 default), so
     * a high byte decodes as ANSEL rather than passing through as raw bytes.
     */
    #[Test]
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
     */
    #[Test]
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

        $joined = implode('', $values);

        // The acute IS decoded (it lands on its own line), but because the reader decodes per
        // physical line before CONC assembly it never composes with the base to "é".
        self::assertStringContainsString("\u{0301}", $joined, 'the acute mark is decoded, just not composed');
        self::assertStringNotContainsString('é', $joined);
    }

    /**
     * A file declaring CHAR ANSI (a common non-5.5.1 value from Windows exports) is decoded as
     * Windows-1252, not silently mangled as the ANSEL default.
     */
    #[Test]
    public function decodesAnsiAsWindows1252(): void
    {
        // 0xE9 is é in Windows-1252 but a combining caron in ANSEL.
        $stream = $this->rewoundStream("0 HEAD\n1 CHAR ANSI\n0 @I1@ INDI\n1 NAME Caf\xE9\n0 TRLR\n");

        self::assertSame('Café', $this->firstNameValue($stream));
    }

    /**
     * A multi-word CHAR value (e.g. "IBM WINDOWS", used by real exporters — see
     * KennedyFamily.ged) is captured in full and decoded as Windows-1252, not truncated to
     * its first token and mangled as ANSEL.
     */
    #[Test]
    public function decodesMultiWordIbmWindowsAsWindows1252(): void
    {
        $stream = $this->rewoundStream("0 HEAD\n1 CHAR IBM WINDOWS\n0 @I1@ INDI\n1 NAME Caf\xE9\n0 TRLR\n");

        self::assertSame('Café', $this->firstNameValue($stream));
    }

    /**
     * The HEAD.CHAR declaration is detected on a CR-only (classic-Mac) file too, so its
     * charset is honoured rather than defaulting to ANSEL — the /m anchor would only see LF.
     */
    #[Test]
    public function detectsCharOnCrOnlyLineEndings(): void
    {
        $stream = $this->rewoundStream("0 HEAD\r1 CHAR ANSI\r0 @I1@ INDI\r1 NAME Caf\xE9\r0 TRLR\r");

        self::assertSame('Café', $this->firstNameValue($stream));
    }

    /**
     * The HEAD.CHAR value is resolved correctly even when the stream delivers it one byte at
     * a time: the sniff must wait for the terminated value rather than accepting a truncated
     * prefix (e.g. "A" of "ANSI") that would mis-detect the encoding as the ANSEL default.
     */
    #[Test]
    public function resolvesCharThatArrivesInSingleByteReads(): void
    {
        $stream = $this->oneByteStream("0 HEAD\n1 CHAR ANSI\n0 @I1@ INDI\n1 NAME Caf\xE9\n0 TRLR\n");

        self::assertSame('Café', $this->firstNameValue($stream));
    }

    /**
     * A present but unrecognised CHAR value (neither a 5.5.1 charset nor a Windows label,
     * e.g. MACINTOSH) falls back to the 5.5.1 default character set, ANSEL.
     */
    #[Test]
    public function fallsBackToAnselForAnUnrecognisedCharValue(): void
    {
        // 0xCF is ANSEL "es zet"; under any pass-through it would be invalid UTF-8.
        $stream = $this->rewoundStream("0 HEAD\n1 CHAR MACINTOSH\n0 @I1@ INDI\n1 NAME \xCF\n0 TRLR\n");

        self::assertSame('ß', $this->firstNameValue($stream));
    }

    /**
     * An astral character (a surrogate pair) whose halves fall on either side of a read-chunk
     * boundary must not be corrupted — the reader holds a lone high surrogate back until its
     * low half arrives. Reading one byte at a time forces the split.
     */
    #[Test]
    public function decodesAstralCharacterSplitAcrossChunkBoundary(): void
    {
        $utf8  = "0 @I1@ INDI\n1 NAME \u{1F600}X\n0 TRLR\n";
        $bytes = "\xFF\xFE" . (string) mb_convert_encoding($utf8, 'UTF-16LE', 'UTF-8');

        $name = $this->firstNameValue($this->oneByteStream($bytes));

        // Pin the whole value: the trailing "X" proves the surrogate-carry flush resumes after
        // the astral character without dropping or duplicating the following code unit.
        self::assertSame("\u{1F600}X", $name);
    }

    /**
     * A byte-framed stream that declares CHAR UNICODE but has no BOM is undetectable as
     * UTF-16 and is rejected rather than silently mis-parsed.
     */
    #[Test]
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
     * The CHAR line may be the final, terminator-less line of the stream. At genuine EOF the
     * sniff must still resolve it from the buffered header rather than defaulting to ANSEL.
     */
    #[Test]
    public function resolvesUnterminatedFinalCharLine(): void
    {
        $stream = $this->rewoundStream("0 HEAD\n1 CHAR UNICODE");
        $reader = new Reader($stream);

        $this->expectException(UnsupportedEncodingException::class);

        while ($reader->read()) {
            // The unterminated CHAR UNICODE line must be resolved and rejected at EOF.
        }
    }

    /**
     * A complete but terminator-less CHAR line that ends the stream exactly on the sniff limit
     * is still honoured. Because the EOF flag lags a read behind the buffered bytes, the cap
     * branch pulls once to confirm end of stream and then resolves the value rather than
     * defaulting to ANSEL. Exercises the EOF-lag path of the sniff cap.
     */
    #[Test]
    public function resolvesCompleteCharLineEndingExactlyOnTheSniffLimit(): void
    {
        // 7 + 10 + 7 + 65499 + 1 + 12 = 65536 bytes: the stream ends on a complete, unterminated
        // "1 CHAR UTF-8" line whose final byte lands exactly on the 64 KB sniff cap.
        $bytes = "0 HEAD\n1 NAME \xC2\xA9\n1 SOUR " . str_repeat('A', 65499) . "\n1 CHAR UTF-8";
        self::assertSame(65536, strlen($bytes));

        self::assertSame('©', $this->firstNameValue($this->rewoundStream($bytes)));
    }

    /**
     * A CHAR declaration that only appears beyond the 64 KB sniff window is not honoured: the
     * reader stops scanning at the cap and falls back to the ANSEL default rather than reading
     * an unbounded header. The 0xCF byte then decodes to "ß" under ANSEL.
     */
    #[Test]
    public function fallsBackToAnselWhenCharLiesBeyondTheSniffLimit(): void
    {
        $header = "0 HEAD\n" . str_repeat("1 NOTE padding\n", 5000);
        $bytes  = $header . "1 CHAR UTF-8\n0 @I1@ INDI\n1 NAME \xCF\n0 TRLR\n";

        self::assertSame('ß', $this->firstNameValue($this->rewoundStream($bytes)));
    }

    /**
     * A CHAR value whose bytes fill the sniff window while its terminator lands just beyond the
     * 64 KB cap must not be accepted: from within the window the reader cannot tell a complete
     * value ("UTF-8") apart from the prefix of a longer one, so the bounded sniff falls back to
     * ANSEL rather than honouring a possibly-truncated declaration. This pins the fix — the
     * earlier unbounded sniff accepted the in-window value as UTF-8 (leaving the © intact).
     */
    #[Test]
    public function rejectsCharValueWhoseTerminatorFallsBeyondTheSniffLimit(): void
    {
        // "UTF-8" ends exactly on the 64 KB cap; its terminator and the trailing record lie
        // beyond it (7 + 10 + 7 + 65499 + 1 + 12 = 65536, then "\n0 TRLR\n").
        $bytes = "0 HEAD\n1 NAME \xC2\xA9\n1 SOUR " . str_repeat('A', 65499) . "\n1 CHAR UTF-8\n0 TRLR\n";
        self::assertSame(65544, strlen($bytes));

        // Reading one byte at a time pauses the buffer exactly on the cap regardless of the
        // internal chunk size, so the overrun branch fires deterministically. The UTF-8 © must
        // not survive: the two bytes 0xC2 0xA9 decode under the ANSEL fallback to ℗ (U+2117) and
        // ♭ (U+266D). The pre-bounded sniff resolved UTF-8 and returned "©", so this is red there.
        self::assertSame("\u{2117}\u{266D}", $this->firstNameValue($this->oneByteStream($bytes)));
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
