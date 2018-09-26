<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Reader;
use PHPUnit\Framework\TestCase;

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
     *
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage No such file or directory
     */
    public function openFileNotFound()
    {
        new Reader(__DIR__ . '/files/file-note-found.ged');
    }

    /**
     * @test
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Can only read .ged files.
     */
    public function openWithInvalidFilename()
    {
        new Reader(__DIR__ . '/files/not-supported-file.txt');
    }

    /**
     * @test
     */
    public function open()
    {
        $reader = new Reader(__DIR__ . '/files/simple.ged');

        self::assertInstanceOf(Reader::class, $reader);
    }

    /**
     * @test
     */
    public function back()
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
    public function identifier()
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
    public function level()
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
    public function tag()
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
