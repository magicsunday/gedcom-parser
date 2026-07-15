<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Exception\InvalidArchiveException;
use MagicSunday\Gedcom\Exception\MissingGedcomEntryException;
use MagicSunday\Gedcom\GedcomZipReader;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Stream;
use MagicSunday\Gedcom\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

use function file_get_contents;
use function fopen;
use function fwrite;
use function rewind;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * Tests the GEDZIP (`.gdz`) archive reader: it locates the mandated `gedcom.ged` entry and parses it
 * into the typed GedcomDocument through the ordinary pipeline.
 *
 * The bundled fixture `tests/files/minimal.gdz` is a ZIP archive whose single `gedcom.ged` entry
 * carries a minimal GEDCOM 7.0 dataset: a `HEAD` (GEDC.VERS 7.0 and SOUR magicsunday), one
 * `INDI @I1@` named `John /Doe/`, and a `TRLR`.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomZipReader::class)]
#[RequiresPhpExtension('zip')]
class GedcomZipReaderTest extends TestCase
{
    /**
     * Reading a `.gdz` file by path extracts its `gedcom.ged` entry and parses it into the typed
     * document exactly as the equivalent plain `.ged` stream would.
     */
    #[Test]
    public function readsAGedzipArchiveFromAPath(): void
    {
        $document = GedcomZipReader::readFile(__DIR__ . '/files/minimal.gdz');

        $this->assertMinimalDocument($document);
    }

    /**
     * A `.gdz` archive supplied as a stream is spooled to a temporary file, opened and parsed,
     * yielding the same typed document as the by-path reader.
     */
    #[Test]
    public function readsAGedzipArchiveFromAStream(): void
    {
        $stream = (new StreamFactory())->createStreamFromFile(__DIR__ . '/files/minimal.gdz');

        $document = GedcomZipReader::read($stream);

        $this->assertMinimalDocument($document);
    }

    /**
     * A non-seekable archive stream — the very reason the stream reader spools to a temporary file —
     * is still read: the spool consumes it forward without rewinding (which a non-seekable stream
     * would refuse), so the document parses just as from a seekable source.
     */
    #[Test]
    public function readsAGedzipArchiveFromANonSeekableStream(): void
    {
        $resource = fopen('php://temp', 'r+b');
        self::assertIsResource($resource);

        $bytes = file_get_contents(__DIR__ . '/files/minimal.gdz');
        self::assertIsString($bytes);
        fwrite($resource, $bytes);
        rewind($resource);

        // Reporting non-seekable makes the inherited Stream::seek()/rewind() throw, so the spool
        // must consume the stream via read() alone rather than rewinding it.
        $stream = new class($resource) extends Stream {
            public function isSeekable(): bool
            {
                return false;
            }
        };
        self::assertFalse($stream->isSeekable(), 'the stream must report itself as non-seekable');

        $document = GedcomZipReader::read($stream);

        $this->assertMinimalDocument($document);
    }

    /**
     * A file that is not a valid ZIP archive (here a plain `.ged` file) fails loud rather than
     * being misread.
     */
    #[Test]
    public function rejectsAFileThatIsNotAZipArchive(): void
    {
        $this->expectException(InvalidArchiveException::class);

        GedcomZipReader::readFile(__DIR__ . '/files/BachFamily.ged');
    }

    /**
     * A ZIP archive that lacks the mandated `gedcom.ged` entry fails loud, since it carries no
     * GEDCOM dataset.
     */
    #[Test]
    public function rejectsAnArchiveWithoutTheMandatedGedcomEntry(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'gdz');
        self::assertIsString($path);

        $archive = new ZipArchive();
        self::assertTrue($archive->open($path, ZipArchive::OVERWRITE));
        $archive->addFromString('other.txt', 'not a gedcom dataset');
        $archive->close();

        try {
            $this->expectException(MissingGedcomEntryException::class);

            GedcomZipReader::readFile($path);
        } finally {
            unlink($path);
        }
    }

    /**
     * Asserts the parsed document matches the bundled `minimal.gdz` fixture's dataset.
     *
     * @param GedcomDocument $document The parsed document.
     *
     * @return void
     */
    private function assertMinimalDocument(GedcomDocument $document): void
    {
        self::assertCount(1, $document->individuals);
        self::assertSame('I1', $document->individuals[0]->xref);
        self::assertSame('John /Doe/', $document->individuals[0]->name[0]->value);
        self::assertSame('John Doe', $document->individuals[0]->name[0]->getDisplayName());
    }
}
