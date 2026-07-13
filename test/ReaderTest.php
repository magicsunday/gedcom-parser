<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Exception\ExceptionInterface;
use MagicSunday\Gedcom\Exception\StreamException;
use MagicSunday\Gedcom\Exception\UnsupportedFileException;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit test.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ReaderTest extends TestCase
{
    /**
     * Opening a missing file raises a domain StreamException.
     */
    #[Test]
    public function openFileNotFound(): void
    {
        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('The file ' . __DIR__ . '/files/file-note-found.ged cannot be opened.');

        (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/file-note-found.ged');
    }

    /**
     * A non-.ged file is rejected with a domain UnsupportedFileException.
     */
    #[Test]
    public function openWithInvalidFilename(): void
    {
        $this->expectException(UnsupportedFileException::class);
        $this->expectExceptionMessage('Can only read .ged files.');

        new Reader((new StreamFactory())->createStreamFromFile(__DIR__ . '/files/not-supported-file.txt'));
    }

    /**
     * Failures from different subsystems — a non-.ged file and a missing file — are both
     * catchable through the single ExceptionInterface group, even though they extend
     * different SPL base classes.
     */
    #[Test]
    public function failuresAcrossSubsystemsShareExceptionInterface(): void
    {
        $cases = [
            [__DIR__ . '/files/not-supported-file.txt', UnsupportedFileException::class],
            [__DIR__ . '/files/file-note-found.ged', StreamException::class],
        ];

        foreach ($cases as [$file, $expected]) {
            try {
                new Reader((new StreamFactory())->createStreamFromFile($file));

                self::fail('Expected an ' . ExceptionInterface::class . ' for ' . $file);
            } catch (ExceptionInterface $exception) {
                self::assertInstanceOf($expected, $exception);
            }
        }
    }

    /**
     * Constructing a reader over a valid .ged fixture yields a Reader instance.
     */
    #[Test]
    public function open(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        self::assertInstanceOf(Reader::class, $reader);
    }

    /**
     * back() rewinds the cursor so the next read re-serves the previously read line.
     */
    #[Test]
    public function back(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        // Read two lines
        $reader->read();
        $reader->read();

        $line1 = $reader->current();

        // Move the cursor one line back and reread the line
        $reader->back();
        $reader->read();

        $line2 = $reader->current();

        self::assertSame($line1, $line2);
    }

    /**
     * A level-0 INDI record's cross-reference identifier round-trips back into its line.
     */
    #[Test]
    public function identifierRoundTripsIntoIndiRecordLine(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        $found = false;

        while ($reader->read()) {
            if (($reader->level() === 0) && ($reader->tag() === 'INDI')) {
                $found = true;

                self::assertSame('0 @' . $reader->identifier() . '@ INDI', trim($reader->current()));
            }
        }

        self::assertTrue($found, 'Expected at least one INDI record in simple.ged');
    }

    /**
     * level() reports the GEDCOM level of the current line for records and nested substructures.
     */
    #[Test]
    public function level(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        // Read to the first INDI record
        while ($reader->read()) {
            if ($reader->value() === 'INDI') {
                self::assertSame(0, $reader->level());
            }

            if ($reader->tag() === 'HEAD') {
                self::assertSame(0, $reader->level());
            }

            if ($reader->tag() === 'GEDC') {
                self::assertSame(1, $reader->level());
            }

            if ($reader->tag() === 'VERS') {
                self::assertSame(2, $reader->level());
            }

            if ($reader->tag() === 'TRLR') {
                self::assertSame(0, $reader->level());
            }
        }
    }

    /**
     * tag() reports the tag name of the current line as it advances through the record.
     */
    #[Test]
    public function tag(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        // Read to the first INDI record
        while ($reader->read()) {
            if ($reader->tag() === 'HEAD') {
                $this->addToAssertionCount(1);
            }

            if ($reader->tag() === 'GEDC') {
                $this->addToAssertionCount(1);
            }

            if ($reader->tag() === 'VERS') {
                $this->addToAssertionCount(1);
            }

            if ($reader->tag() === 'TRLR') {
                $this->addToAssertionCount(1);
            }
        }
    }
}
