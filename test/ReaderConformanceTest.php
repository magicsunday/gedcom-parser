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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Conformance tests for the low-level line tokeniser against the GEDCOM 5.5.1 grammar.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Reader::class)]
#[CoversClass(UnableToParseLineException::class)]
class ReaderConformanceTest extends TestCase
{
    /**
     * Creates a rewound reader over the given raw GEDCOM string.
     *
     * @param string $gedcom the raw GEDCOM document to wrap
     *
     * @return Reader a reader positioned at the start of the given document
     */
    private function reader(string $gedcom): Reader
    {
        $stream = (new StreamFactory())->createStream($gedcom);
        $stream->rewind();

        return new Reader($stream);
    }

    /**
     * A level number may be one or two digits (0-99); a two-digit level must parse.
     */
    #[Test]
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
     */
    #[Test]
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
     */
    #[Test]
    public function rejectsInvalidTagCharacter(): void
    {
        try {
            $this->reader("1 FOO.BAR value\n")->read();

            self::fail('Expected ' . UnableToParseLineException::class . ' was not thrown.');
        } catch (UnableToParseLineException $exception) {
            self::assertStringContainsString('FOO.BAR', $exception->getRawLine());
        }
    }

    /**
     * A leading UTF-8 byte-order mark on the first line is removed so the line still parses.
     */
    #[Test]
    public function stripsLeadingUtf8Bom(): void
    {
        $reader = $this->reader("\xEF\xBB\xBF0 HEAD\n");

        $reader->read();

        self::assertSame(0, $reader->level());
        self::assertSame('HEAD', $reader->tag());
    }

    /**
     * Bytes inside a value that happen to be part of the BOM byte sequence (here the
     * trailing 0xBB of "»") must survive; the BOM is only stripped as a leading prefix,
     * never trimmed from the end of every line.
     */
    #[Test]
    public function preservesTrailingBytesThatCollideWithTheBomMask(): void
    {
        // A leading UTF-8 BOM selects UTF-8 (pass-through); the value ends in » (0xC2 0xBB),
        // whose trailing 0xBB collides with the BOM mask but must survive — the BOM is only
        // consumed as a leading prefix, never trimmed from a value.
        $reader = $this->reader("\xEF\xBB\xBF1 NOTE ab\xC2\xBB");

        $reader->read();

        self::assertSame("ab\xC2\xBB", $reader->value());
    }

    /**
     * A line without a cross-reference identifier reports NULL, consistent with xref()
     * and value() and honouring the nullable return type.
     */
    #[Test]
    public function identifierReturnsNullWhenAbsent(): void
    {
        $reader = $this->reader("1 NAME John\n");

        $reader->read();

        self::assertNull($reader->identifier());
    }

    /**
     * A blank line following a record must not leak the previous line's identifier,
     * cross-reference or value into the accessors.
     */
    #[Test]
    public function doesNotLeakIdentifierAcrossBlankLine(): void
    {
        $reader = $this->reader("0 @I1@ INDI\n   \n");

        $reader->read();

        self::assertSame('I1', $reader->identifier());

        $reader->read();

        self::assertNull($reader->identifier());
        self::assertNull($reader->xref());
        self::assertNull($reader->value());
    }

    /**
     * A DATE value carrying a calendar escape (@#DJULIAN@ …) is kept verbatim in the
     * value; the escape is text, not a cross-reference pointer, so the calendar is
     * never torn off and silently reinterpreted as Gregorian.
     */
    #[Test]
    public function keepsCalendarEscapeInDateValue(): void
    {
        $reader = $this->reader("2 DATE @#DJULIAN@ 14 FEB 1732\n");

        $reader->read();

        self::assertNull($reader->xref());
        self::assertSame('@#DJULIAN@ 14 FEB 1732', $reader->value());
    }

    /**
     * A value that is exactly a cross-reference pointer (first character alphanumeric)
     * is exposed as the xref, not as a text value.
     */
    #[Test]
    public function parsesWholeValuePointer(): void
    {
        $reader = $this->reader("1 FAMS @F1@\n");

        $reader->read();

        self::assertSame('F1', $reader->xref());
        self::assertNull($reader->value());
    }

    /**
     * A literal at-sign inside a value is written doubled (@@) per the grammar and must
     * be decoded back to a single @.
     */
    #[Test]
    public function decodesDoubledAtInValue(): void
    {
        $reader = $this->reader("1 EMAIL john@@example.com\n");

        $reader->read();

        self::assertSame('john@example.com', $reader->value());
    }

    /**
     * A value that legitimately begins with an at-sign is encoded @@… and decodes to a
     * single leading @ (it is not mistaken for a pointer).
     */
    #[Test]
    public function decodesLeadingDoubledAt(): void
    {
        $reader = $this->reader("1 NOTE @@start\n");

        $reader->read();

        self::assertNull($reader->xref());
        self::assertSame('@start', $reader->value());
    }
}
