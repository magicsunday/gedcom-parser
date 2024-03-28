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
        $this->expectExceptionMessage('No such file or directory');
        $this->expectException(RuntimeException::class);
        new Reader(__DIR__ . '/files/file-note-found.ged');
    }

    /**
     * @test
     */
    public function openWithInvalidFilename(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can only read .ged files.');

        new Reader(__DIR__ . '/files/not-supported-file.txt');
    }

    /**
     * @test
     */
    public function open(): void
    {
        $reader = new Reader(__DIR__ . '/files/simple.ged');

        self::assertInstanceOf(Reader::class, $reader);
    }

    /**
     * @test
     */
    public function back(): void
    {
        $reader = new Reader(__DIR__ . '/files/simple.ged');

        // Read two lines
        $reader->read();
        $reader->read();

        $line1 = $reader->current();

        // Move cursor one line back and reread the line
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
        $reader = new Reader(__DIR__ . '/files/simple.ged');

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
        $reader = new Reader(__DIR__ . '/files/simple.ged');

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
        $reader = new Reader(__DIR__ . '/files/simple.ged');

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
