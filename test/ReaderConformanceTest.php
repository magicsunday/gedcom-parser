<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Exception\UnableToParseLineException;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\TestCase;

/**
 * Conformance tests for the low-level line tokeniser against the GEDCOM 5.5.1 grammar.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 *
 * @covers \MagicSunday\Gedcom\Reader
 * @covers \MagicSunday\Gedcom\Exception\UnableToParseLineException
 */
class ReaderConformanceTest extends TestCase
{
    /**
     * Creates a rewound reader over the given raw GEDCOM string.
     *
     * @param string $gedcom The raw GEDCOM document to wrap.
     *
     * @return Reader A reader positioned at the start of the given document.
     */
    private function reader(string $gedcom): Reader
    {
        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return new Reader($stream);
    }

    /**
     * A level number may be one or two digits (0-99); a two-digit level must parse.
     *
     * @test
     */
    public function readsTwoDigitLevel(): void
    {
        $reader = $this->reader("0 @I1@ INDI\n1 BIRT\n10 NOTE deep\n");

        $reader->read();
        $reader->read();
        $reader->read();

        self::assertSame(10, $reader->level());
        self::assertSame('NOTE', $reader->tag());
        self::assertSame('deep', $reader->value());
    }

    /**
     * A level number must not contain a leading zero (0-99, "not 01"); such a line is
     * rejected with a specific exception carrying the offending line and its number.
     *
     * @test
     */
    public function rejectsLeadingZeroLevel(): void
    {
        try {
            $this->reader("01 NAME John\n")->read();

            self::fail('Expected ' . UnableToParseLineException::class . ' was not thrown.');
        } catch (UnableToParseLineException $exception) {
            self::assertSame(1, $exception->getLineNumber());
            self::assertStringContainsString('01 NAME John', $exception->getRawLine());
        }
    }

    /**
     * A tag may only contain the characters A-Z, a-z, 0-9 and underscore; a tag holding
     * any other character (here a dot) is not a valid GEDCOM tag and the line is rejected.
     *
     * @test
     */
    public function rejectsInvalidTagCharacter(): void
    {
        try {
            $this->reader("1 FOO.BAR value\n")->read();

            self::fail('Expected ' . UnableToParseLineException::class . ' was not thrown.');
        } catch (UnableToParseLineException $exception) {
            self::assertStringContainsString('FOO.BAR', $exception->getRawLine());
        }
    }
}
