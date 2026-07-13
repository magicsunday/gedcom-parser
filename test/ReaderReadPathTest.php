<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Exception\LineTooLongException;
use MagicSunday\Gedcom\Exception\UnsupportedFileException;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Stream;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function count;
use function fopen;
use function fwrite;
use function rewind;
use function str_repeat;
use function str_replace;

/**
 * Tests the low-level read path: line-terminator handling (#11), non-seekable streams (#12)
 * and the line push-back that replaces seek-based rewind.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Reader::class)]
class ReaderReadPathTest extends TestCase
{
    /**
     * All four GEDCOM 5.5.1 line terminators (CR, LF, CRLF, LFCR) must parse a fixture into
     * the identical, non-empty individual count. Today a CR-only file yields zero records
     * because the reader splits on LF only.
     */
    #[Test]
    public function parsesAllLineEndingsIdentically(): void
    {
        $baseline = $this->countIndividuals(__DIR__ . '/files/LTERLF.ged');

        self::assertGreaterThan(0, $baseline, 'The LF baseline fixture must contain individuals.');

        foreach (['LTERCR', 'LTERCRLF', 'LTERLFCR'] as $name) {
            self::assertSame(
                $baseline,
                $this->countIndividuals(__DIR__ . '/files/' . $name . '.ged'),
                $name . '.ged must parse to the same individual count as the LF baseline.'
            );
        }
    }

    /**
     * A non-file STDIO stream whose metadata URI IS a string (php://stdin, php://fd/N — the
     * canonical piped input) must not be rejected either: the .ged guard applies only to a
     * real on-disk file (wrapper_type "plainfile"), not to a php:// wrapper.
     */
    #[Test]
    public function acceptsNonFileStreamWithStringUri(): void
    {
        $resource = fopen('php://stdin', 'r');

        self::assertIsResource($resource);

        try {
            // Constructing the reader only inspects stream metadata; it does not read stdin.
            new Reader(new Stream($resource));
        } catch (UnsupportedFileException $exception) {
            self::fail('A php:// stream URI must not be rejected by the .ged guard: ' . $exception->getMessage());
        }
    }

    /**
     * A non-seekable readable stream must parse its records; before the refactor the reader
     * bailed out of read() on the first call because the stream was not seekable, silently
     * yielding an empty document.
     */
    #[Test]
    public function parsesNonSeekableStream(): void
    {
        // A resource positioned at the start whose wrapping stream reports itself as
        // non-seekable — the reader must consume it via read() without ever seeking.
        $resource = fopen('php://temp', 'r+');

        self::assertIsResource($resource);

        fwrite($resource, "0 @I1@ INDI\n0 @I2@ INDI\n0 TRLR\n");
        rewind($resource);

        // Reporting non-seekable is enough to make the inherited Stream::seek() throw, so any
        // seek attempt during parsing fails the test: the reader must consume the stream via
        // read() alone.
        $stream = new class($resource) extends Stream {
            public function isSeekable(): bool
            {
                return false;
            }
        };

        self::assertFalse($stream->isSeekable(), 'The stream must report itself as non-seekable.');
        self::assertCount(2, (new Parser($stream))->parse()->getIndividual());
    }

    /**
     * A terminator pair split across a read-chunk boundary must not produce a phantom blank
     * line. Reading one byte at a time forces every CRLF/LFCR pair to straddle a boundary;
     * the parsed record count must still match the LF baseline.
     */
    #[Test]
    public function parsesLineEndingSplitAcrossChunkBoundary(): void
    {
        $records  = "0 @I1@ INDI\n1 NAME John /Smith/\n0 @I2@ INDI\n1 NAME Jane /Doe/\n0 TRLR\n";
        $baseline = $this->countIndividualsFromStream($this->oneByteStream($records));

        self::assertSame(2, $baseline);

        foreach (["\r\n", "\r", "\n\r"] as $terminator) {
            $content = str_replace("\n", $terminator, $records);

            self::assertSame(
                $baseline,
                $this->countIndividualsFromStream($this->oneByteStream($content)),
                'A terminator split across a chunk boundary must not drop or add records.'
            );
        }
    }

    /**
     * back() re-reads the previous line without advancing the line count for the re-read.
     */
    #[Test]
    public function backReReadsThePreviousLine(): void
    {
        $stream = (new StreamFactory())->createStream("0 HEAD\n1 SOUR X\n0 TRLR\n");
        $stream->rewind();

        $reader = new Reader($stream);
        $reader->read();
        $reader->read();

        $line     = $reader->current();
        $afterTwo = $reader->count();

        $reader->back();
        $reader->read();

        self::assertSame($line, $reader->current(), 'back() then read() must re-serve the same line.');
        self::assertSame($afterTwo, $reader->count(), 'A re-served push-back line must not advance the line count.');
    }

    /**
     * A line can be put back at most once: a second back() without an intervening read is a
     * no-op, so the single push-back slot cannot be overwritten.
     */
    #[Test]
    public function backTwiceWithoutReadIsNoOp(): void
    {
        $stream = (new StreamFactory())->createStream("0 HEAD\n1 SOUR X\n0 TRLR\n");
        $stream->rewind();

        $reader = new Reader($stream);
        $reader->read();

        self::assertTrue($reader->back(), 'The first back() after a read must succeed.');
        self::assertFalse($reader->back(), 'A second back() without an intervening read must be a no-op.');
    }

    /**
     * count() reports the number of real lines read, not the end-of-stream sentinel: draining
     * a two-line document leaves the count at 2, not 3.
     */
    #[Test]
    public function countReportsRealLinesNotTheEndOfStream(): void
    {
        $stream = (new StreamFactory())->createStream("0 HEAD\n0 TRLR\n");
        $stream->rewind();

        $reader = new Reader($stream);

        while ($reader->read()) {
            // Drain the reader.
        }

        self::assertSame(2, $reader->count());
    }

    /**
     * A read that momentarily yields no bytes while the stream is not yet at its end (a
     * non-blocking or slow stream, per the PSR-7 read() contract) is retried, not treated as
     * end of stream — otherwise the records that follow would be silently dropped.
     */
    #[Test]
    public function retriesReadWhenNoBytesAreMomentarilyAvailable(): void
    {
        $stream = new class("0 @I1@ INDI\n0 TRLR\n") extends Stream {
            private int $reads = 0;

            public function __construct(string $content)
            {
                parent::__construct('php://temp', 'r+');

                $this->write($content);
                $this->rewind();
            }

            public function read(int $length): string
            {
                // The first read yields no bytes yet, while eof() is still false; the real
                // data only arrives on the following read.
                if ($this->reads++ === 0) {
                    return '';
                }

                return parent::read($length);
            }
        };

        self::assertCount(1, (new Parser($stream))->parse()->getIndividual());
    }

    /**
     * back() is a no-op once the stream is exhausted: the end-of-stream empty line cannot be
     * put back, and a following read() still reports end of stream.
     */
    #[Test]
    public function backAtEndOfStreamIsNoOp(): void
    {
        $stream = (new StreamFactory())->createStream("0 HEAD\n0 TRLR\n");
        $stream->rewind();

        $reader = new Reader($stream);

        while ($reader->read()) {
            // Drain the reader to the end of the stream.
        }

        self::assertFalse($reader->back(), 'back() at end of stream must be a no-op.');
        self::assertFalse($reader->read(), 'read() after an end-of-stream back() must still report EOF.');
    }

    /**
     * back() before any read is a no-op: it returns false and does not terminate the reader
     * (the first line is still served on the next read).
     */
    #[Test]
    public function backBeforeAnyReadIsNoOp(): void
    {
        $stream = (new StreamFactory())->createStream("0 HEAD\n0 TRLR\n");
        $stream->rewind();

        $reader = new Reader($stream);

        self::assertFalse($reader->back(), 'back() before any read must be a no-op.');
        self::assertTrue($reader->read(), 'The first line must still be readable after a leading back().');
        self::assertSame(1, $reader->count());
        self::assertSame('HEAD', $reader->tag());
    }

    /**
     * A document whose final line carries no terminator parses the same as the terminated
     * form, exercising the end-of-stream drain of the trailing partial line.
     */
    #[Test]
    public function parsesFinalLineWithoutTrailingTerminator(): void
    {
        // The final line is a meaningful record, so an EOF drain that dropped it would lower
        // the individual count from 2 to 1 and fail the assertion.
        $terminated   = $this->countIndividualsFromStream($this->oneByteStream("0 @I1@ INDI\n0 @I2@ INDI\n"));
        $unterminated = $this->countIndividualsFromStream($this->oneByteStream("0 @I1@ INDI\n0 @I2@ INDI"));

        self::assertSame(2, $terminated);
        self::assertSame($terminated, $unterminated, 'A missing final terminator must not drop the last record.');
    }

    /**
     * A single physical line exceeding the maximum length (no terminator on a hostile or
     * malformed stream) must be rejected instead of materialising the whole stream in
     * memory.
     */
    #[Test]
    public function throwsOnLineExceedingMaxLength(): void
    {
        // A structurally valid, terminator-less line whose value is far larger than any
        // real GEDCOM line — without a bound this would parse as one giant NOTE record.
        $content = '0 NOTE ' . str_repeat('A', Reader::MAX_LINE_LENGTH + 1024);

        $stream = (new StreamFactory())->createStream($content);
        $stream->rewind();

        $reader = new Reader($stream);

        try {
            while ($reader->read()) {
                // Drain the reader; the oversized line must trigger the exception.
            }

            self::fail('Expected ' . LineTooLongException::class . ' was not thrown.');
        } catch (LineTooLongException $exception) {
            self::assertSame(1, $exception->getLineNumber());
            self::assertStringContainsString((string) Reader::MAX_LINE_LENGTH, $exception->getMessage());
        }
    }

    /**
     * An oversized line is rejected even when it DOES carry a terminator that happens to
     * arrive in the same chunk that pushes the buffer past the bound.
     */
    #[Test]
    public function throwsOnTerminatedLineExceedingMaxLength(): void
    {
        $content = '0 NOTE ' . str_repeat('A', Reader::MAX_LINE_LENGTH + 1024) . "\n0 TRLR\n";

        $stream = (new StreamFactory())->createStream($content);
        $stream->rewind();

        $reader = new Reader($stream);

        $this->expectException(LineTooLongException::class);

        while ($reader->read()) {
            // The terminated but oversized first line must still throw.
        }
    }

    /**
     * Counts the individuals parsed from a fixture file.
     *
     * @param string $file the absolute path to the GEDCOM fixture
     *
     * @return int the number of parsed individual records
     */
    private function countIndividuals(string $file): int
    {
        $stream = (new StreamFactory())->createStreamFromFile($file);

        return count((new Parser($stream))->parse()->getIndividual());
    }

    /**
     * Counts the individuals parsed from an already-prepared stream.
     *
     * @param Stream $stream a rewound readable stream over a GEDCOM document
     *
     * @return int the number of parsed individual records
     */
    private function countIndividualsFromStream(Stream $stream): int
    {
        return count((new Parser($stream))->parse()->getIndividual());
    }

    /**
     * Wraps a GEDCOM document in a stream whose read() yields at most one byte per call,
     * forcing every terminator pair to straddle a chunk boundary.
     *
     * @param string $content the raw GEDCOM document
     *
     * @return Stream a rewound single-byte-per-read stream
     */
    private function oneByteStream(string $content): Stream
    {
        $stream = new class($content) extends Stream {
            public function __construct(string $content)
            {
                parent::__construct('php://temp', 'r+');

                $this->write($content);
                $this->rewind();
            }

            public function read(int $length): string
            {
                return parent::read(1);
            }
        };

        return $stream;
    }
}
