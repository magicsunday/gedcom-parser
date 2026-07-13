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
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Stream;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\TestCase;

use function count;
use function escapeshellarg;
use function popen;
use function str_repeat;

/**
 * Tests the low-level read path: line-terminator handling (#11), non-seekable streams (#12)
 * and the line push-back that replaces seek-based rewind.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 *
 * @covers \MagicSunday\Gedcom\Reader
 */
class ReaderReadPathTest extends TestCase
{
    /**
     * All four GEDCOM 5.5.1 line terminators (CR, LF, CRLF, LFCR) must parse a fixture into
     * the identical, non-empty individual count. Today a CR-only file yields zero records
     * because the reader splits on LF only.
     *
     * @test
     */
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
     * A non-file STDIO stream (a pipe whose metadata URI is null) must not be rejected by
     * the .ged-extension guard — the extension is only meaningful for an actual filename.
     *
     * @test
     */
    public function acceptsNonFileStreamWithoutGedExtension(): void
    {
        $stream = new Stream($this->openPipe(__DIR__ . '/files/simple.ged'));
        $reader = new Reader($stream);

        self::assertInstanceOf(Reader::class, $reader);

        // Drain the pipe so the feeding process finishes cleanly (no SIGPIPE noise).
        while ($reader->read()) {
            // Intentionally empty.
        }
    }

    /**
     * A non-seekable readable stream (a pipe) must parse its records; today the reader
     * bails out of read() on the first call because the stream is not seekable, silently
     * yielding an empty document.
     *
     * @test
     */
    public function parsesNonSeekableStream(): void
    {
        $stream = new Stream($this->openPipe(__DIR__ . '/files/simple.ged'));

        self::assertFalse($stream->isSeekable(), 'A pipe must report itself as non-seekable.');

        $individuals = (new Parser($stream))->parse()->getIndividual();

        self::assertSame(
            $this->countIndividuals(__DIR__ . '/files/simple.ged'),
            count($individuals)
        );
        self::assertGreaterThan(0, count($individuals));
    }

    /**
     * A terminator pair split across a read-chunk boundary must not produce a phantom blank
     * line. Reading one byte at a time forces every CRLF/LFCR pair to straddle a boundary;
     * the parsed record count must still match the LF baseline.
     *
     * @test
     */
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
     *
     * @test
     */
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
     * A single physical line exceeding the maximum length (no terminator on a hostile or
     * malformed stream) must be rejected instead of materialising the whole stream in
     * memory.
     *
     * @test
     */
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
            self::assertSame(Reader::MAX_LINE_LENGTH, $exception->getMaxLength());
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
     * Opens a read-only, non-seekable pipe streaming the contents of the given file.
     *
     * @param string $file the absolute path to stream through the pipe
     *
     * @return resource the pipe resource
     */
    private function openPipe(string $file)
    {
        $resource = popen('cat ' . escapeshellarg($file), 'r');

        self::assertIsResource($resource, 'Failed to open the test pipe.');

        return $resource;
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
