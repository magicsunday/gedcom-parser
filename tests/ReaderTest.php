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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit test.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Reader::class)]
#[CoversClass(StreamFactory::class)]
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
     * As the cursor advances through a fixture, level() reports the GEDCOM level of each line by
     * its nesting: the level-0 records (HEAD, TRLR) and the nested GEDC/VERS substructures each
     * report their depth. The tag is read at every line, so the tag accessor is exercised too.
     */
    #[Test]
    public function reportsGedcomLevelForEachStructuralTagAsTheCursorAdvances(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/simple.ged');
        $reader = new Reader($stream);

        $levels = [];

        while ($reader->read()) {
            $tag = $reader->tag();

            // Record the first level seen per structural tag, keyed by the tag the accessor reports.
            if (!array_key_exists($tag, $levels)) {
                $levels[$tag] = $reader->level();
            }
        }

        self::assertSame(0, $levels['HEAD'] ?? null, 'HEAD is a level-0 record');
        self::assertSame(1, $levels['GEDC'] ?? null, 'GEDC nests one level under HEAD');
        self::assertSame(2, $levels['VERS'] ?? null, 'VERS nests two levels under HEAD');
        self::assertSame(0, $levels['TRLR'] ?? null, 'TRLR is a level-0 record');
    }
}
