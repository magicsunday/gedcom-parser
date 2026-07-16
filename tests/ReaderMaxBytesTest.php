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
use MagicSunday\Gedcom\Exception\InputTooLargeException;
use MagicSunday\Gedcom\GedcomZipReader;
use MagicSunday\Gedcom\Mapping\TypedGedcomParser;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use ReflectionProperty;
use ZipArchive;

use function iterator_to_array;
use function str_repeat;
use function strlen;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * Tests the configurable parse byte budget (#123): the reader caps the total number of bytes read
 * from a source at a caller-configurable maximum with a sensible default, so an oversized plain
 * `.ged` upload or a GEDZIP decompression bomb (a tiny archive whose `gedcom.ged` inflates hugely)
 * is aborted with an {@see InputTooLargeException} rather than run to memory exhaustion. The cap is
 * enforced at the single byte-reading choke point, so it covers every read path uniformly.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Reader::class)]
#[CoversClass(InputTooLargeException::class)]
#[UsesClass(Parser::class)]
#[UsesClass(TypedGedcomParser::class)]
#[UsesClass(GedcomZipReader::class)]
#[UsesClass(IndividualRecord::class)]
class ReaderMaxBytesTest extends TestCase
{
    /**
     * A minimal but complete ASCII GEDCOM 5.5.1 dataset used as the byte payload under test.
     */
    private const string GEDCOM = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n0 @I1@ INDI\n1 NAME John /Doe/\n0 TRLR\n";

    /**
     * A reader built without an explicit cap falls back to the default, and that default is 512 MiB —
     * above the tens-to-hundreds-of-MB range a legitimate large tree occupies. Reflecting the
     * resolved cap on a reader constructed with no cap argument pins both the default value and the
     * null-to-default wiring, neither of which a bare constant echo would prove.
     */
    #[Test]
    public function aReaderWithoutACapUsesThe512MiBDefault(): void
    {
        $reader = new Reader($this->stream(self::GEDCOM));

        // Reading the private property yields mixed, so the assertion is not a compile-time tautology
        // against the constant's literal type under PHPStan's max level.
        $maxBytes = (new ReflectionProperty(Reader::class, 'maxBytes'))->getValue($reader);

        self::assertSame(512 * 1024 * 1024, $maxBytes);
    }

    /**
     * Reading a source larger than the configured cap aborts with an {@see InputTooLargeException}
     * that carries the cap.
     */
    #[Test]
    public function readingBeyondTheCapThrows(): void
    {
        $reader = new Reader($this->stream(self::GEDCOM), 10);

        try {
            while ($reader->read()) {
                // Drive the reader to end of stream; the cap must fire first.
            }

            self::fail('Reading a source larger than the cap must throw.');
        } catch (InputTooLargeException $exception) {
            self::assertSame(10, $exception->getMaxBytes());
        }
    }

    /**
     * A cap at or above the total byte length lets the whole source read without aborting.
     */
    #[Test]
    public function readingWithinTheCapSucceeds(): void
    {
        $reader = new Reader($this->stream(self::GEDCOM), strlen(self::GEDCOM));

        $lines = 0;

        while ($reader->read()) {
            ++$lines;
        }

        self::assertGreaterThan(0, $lines, 'A source within the cap must read to the end.');
    }

    /**
     * A non-positive cap is a caller misconfiguration and is rejected up front.
     */
    #[Test]
    public function aNonPositiveCapIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Reader($this->stream(self::GEDCOM), 0);
    }

    /**
     * The plain-`.ged` {@see Parser} path honours the cap, retrofitting the bound onto a hostile
     * upload whose size the transport did not limit.
     */
    #[Test]
    public function theParserPathHonoursTheCap(): void
    {
        $this->expectException(InputTooLargeException::class);

        (new Parser($this->stream(self::GEDCOM), 10))->parse();
    }

    /**
     * The version-fixed {@see TypedGedcomParser} honours the cap on its streaming pass.
     */
    #[Test]
    public function theTypedParserStreamingPathHonoursTheCap(): void
    {
        $parser = TypedGedcomParser::create(GedcomVersion::V551, ['INDI' => IndividualRecord::class]);

        $this->expectException(InputTooLargeException::class);

        iterator_to_array($parser->parse($this->stream(self::GEDCOM), 10));
    }

    /**
     * The version-fixed {@see TypedGedcomParser} honours the cap on its eager document pass.
     */
    #[Test]
    public function theTypedParserDocumentPathHonoursTheCap(): void
    {
        $parser = TypedGedcomParser::create(GedcomVersion::V551, ['INDI' => IndividualRecord::class]);

        $this->expectException(InputTooLargeException::class);

        $parser->parseDocument($this->stream(self::GEDCOM), 10);
    }

    /**
     * A GEDZIP decompression bomb — a tiny archive whose `gedcom.ged` entry inflates far past the
     * cap — is aborted on the decompressed byte count, not the compressed transport size.
     */
    #[Test]
    #[RequiresPhpExtension('zip')]
    public function aGedzipDecompressionBombIsCapped(): void
    {
        // A highly compressible payload: a valid header plus many repeated NOTE lines. It inflates
        // to tens of KiB while the archive on disk stays a few hundred bytes, so a size-capped
        // upload would not bound the parse — only the decompressed-byte cap does.
        $payload = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n1 CHAR ASCII\n0 @I1@ INDI\n"
            . str_repeat('1 NOTE ' . str_repeat('x', 60) . "\n", 1000)
            . "0 TRLR\n";

        $path = $this->writeArchive($payload);

        try {
            $this->expectException(InputTooLargeException::class);

            GedcomZipReader::readFile($path, 1024);
        } finally {
            unlink($path);
        }
    }

    /**
     * A GEDZIP archive whose decompressed dataset stays within the cap still parses normally.
     */
    #[Test]
    #[RequiresPhpExtension('zip')]
    public function aGedzipArchiveWithinTheCapParses(): void
    {
        $path = $this->writeArchive(self::GEDCOM);

        try {
            $document = GedcomZipReader::readFile($path, strlen(self::GEDCOM) + 1024);

            self::assertCount(1, $document->individuals);
        } finally {
            unlink($path);
        }
    }

    /**
     * Wraps the given GEDCOM payload as the mandated `gedcom.ged` entry of a fresh `.gdz` archive on
     * disk and returns its path. The caller unlinks it.
     *
     * @param string $payload The GEDCOM dataset to store as the archive's `gedcom.ged` entry.
     *
     * @return string The path to the written archive.
     */
    private function writeArchive(string $payload): string
    {
        $path = tempnam(sys_get_temp_dir(), 'gdz');
        self::assertIsString($path);

        $archive = new ZipArchive();
        self::assertTrue($archive->open($path, ZipArchive::OVERWRITE));
        $archive->addFromString('gedcom.ged', $payload);
        $archive->close();

        return $path;
    }

    /**
     * Wraps a GEDCOM string in a rewound in-memory stream ready for the reader.
     *
     * @param string $content The GEDCOM bytes.
     *
     * @return StreamInterface The rewound stream.
     */
    private function stream(string $content): StreamInterface
    {
        $stream = (new StreamFactory())->createStream($content);
        $stream->rewind();

        return $stream;
    }
}
