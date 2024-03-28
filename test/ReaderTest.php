<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use InvalidArgumentException;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Unit test.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-reader/
 */
class ReaderTest extends TestCase
{
    /**
     * @test
     */
    public function openFileNotFound(): void
    {
        $this->expectExceptionMessage('The file ' . __DIR__ . '/files/file-note-found.ged cannot be opened.');
        $this->expectException(RuntimeException::class);

        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/file-note-found.ged');
        $reader = new Reader($stream);
    }

    /**
     * @test
     */
    public function openWithInvalidFilename(): void
    {
        $this->expectExceptionMessage('Can only read .ged files.');
        $this->expectException(InvalidArgumentException::class);

        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/not-supported-file.txt');
        $reader = new Reader($stream);
    }

    /**
     * @test
     */
    public function open(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        self::assertInstanceOf(Reader::class, $reader);
    }

    /**
     * @test
     */
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
     * @test
     */
    public function identifier(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        // Read to the first INDI record
        while ($reader->read()) {
            if ($reader->value() === 'INDI') {
                // Grab the identifier
                $id = $reader->identifier();

                self::assertSame($reader->current(), '0 @' . $id . '@ INDI');
            }
        }
    }

    /**
     * @test
     */
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
     * @test
     */
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
