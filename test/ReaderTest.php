<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

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
     * @var Reader
     */
    private $reader;

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
        $this->reader = new Reader(__DIR__ . '/files/simple.ged');

        self::assertInstanceOf(Reader::class, $this->reader);
    }

    /**
     * @test
     */
    public function back()
    {
        $this->reader = new Reader(__DIR__ . '/files/simple.ged');

        // Read two lines
        $this->reader->read();
        $this->reader->read();

        $line1 = $this->reader->current();

        // Move cursor one line back and reread the line
        $this->reader->back();
        $this->reader->read();

        $line2 = $this->reader->current();

        self::assertSame($line1, $line2);
    }

    /**
     * @test
     */
    public function identifier()
    {
        $this->reader = new Reader(__DIR__ . '/files/simple.ged');

        // Read to the first INDI record
        while ($this->reader->read()) {
            if ($this->reader->value() === 'INDI') {
                // Grab the identifier
                $id = $this->reader->identifier();

                self::assertSame($this->reader->current(), '0 @' . $id . '@ INDI');
            }
        }
    }

    /**
     * @test
     */
    public function level()
    {
        $this->reader = new Reader(__DIR__ . '/files/simple.ged');

        // Read to the first INDI record
        while ($this->reader->read()) {

            if ($this->reader->value() === 'INDI') {
                self::assertSame(0, $this->reader->level());
            }

            if ($this->reader->type() === 'HEAD') {
                self::assertSame(0, $this->reader->level());
            }

            if ($this->reader->type() === 'GEDC') {
                self::assertSame(1, $this->reader->level());
            }

            if ($this->reader->type() === 'VERS') {
                self::assertSame(2, $this->reader->level());
            }

            if ($this->reader->type() === 'TRLR') {
                self::assertSame(0, $this->reader->level());
            }
        }
    }

    /**
     * @test
     */
    public function type()
    {
        $this->reader = new Reader(__DIR__ . '/files/simple.ged');

        // Read to the first INDI record
        while ($this->reader->read()) {
            if ($this->reader->type() === 'HEAD') {
                $this->addToAssertionCount(1);
            }

            if ($this->reader->type() === 'GEDC') {
                $this->addToAssertionCount(1);
            }

            if ($this->reader->type() === 'VERS') {
                $this->addToAssertionCount(1);
            }

            if ($this->reader->type() === 'TRLR') {
                $this->addToAssertionCount(1);
            }
        }
    }
}
