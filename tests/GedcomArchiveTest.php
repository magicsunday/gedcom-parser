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
use MagicSunday\Gedcom\Exception\UnableToParseLineException;
use MagicSunday\Gedcom\GedcomArchive;
use MagicSunday\Gedcom\GedcomZipReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

use function base64_decode;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * Tests the open GEDZIP archive handle: it exposes the parsed document and resolves an `OBJE.FILE`
 * payload to the bytes of its embedded archive entry.
 *
 * The bundled fixture `tests/files/media.gdz` is a ZIP archive with a `gedcom.ged` entry (a 7.0
 * dataset whose `OBJE @O1@` references `FILE media/pixel.png`) and an embedded `media/pixel.png`
 * entry holding a 1×1 PNG.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomArchive::class)]
#[CoversClass(GedcomZipReader::class)]
#[RequiresPhpExtension('zip')]
class GedcomArchiveTest extends TestCase
{
    /**
     * The 1×1 PNG embedded in the fixture as `media/pixel.png`.
     */
    private const string PIXEL_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    /**
     * Opening the archive resolves the document's OBJE.FILE reference to the exact embedded bytes.
     */
    #[Test]
    public function resolvesAnEmbeddedMediaFile(): void
    {
        $archive = GedcomZipReader::openArchive(__DIR__ . '/files/media.gdz');

        try {
            self::assertCount(1, $archive->getDocument()->multimedia, 'the fixture holds exactly one multimedia object');
            $reference = $archive->getDocument()->multimedia[0]->file[0]->value;
            self::assertSame('media/pixel.png', $reference);

            $stream = $archive->openFile($reference);
            self::assertNotNull($stream);
            self::assertSame(base64_decode(self::PIXEL_PNG_BASE64, true), $stream->getContents());
        } finally {
            $archive->close();
        }
    }

    /**
     * A FILE reference the classifier rejects (a web URL, or a traversing path that must not reach
     * outside the archive) resolves to NULL, as does a conformant reference to an entry the archive
     * does not contain (a dangling reference) — none is treated as an error.
     */
    #[Test]
    public function returnsNullForNonEmbeddedOrAbsentReferences(): void
    {
        $archive = GedcomZipReader::openArchive(__DIR__ . '/files/media.gdz');

        try {
            self::assertNull($archive->openFile('http://example.com/x.jpg'), 'a web URL is not embedded');
            self::assertNull($archive->openFile('../escape.png'), 'a traversing path must not reach outside the archive');
            self::assertNull($archive->openFile('media/absent.png'), 'a conformant reference to a missing entry is dangling');
        } finally {
            $archive->close();
        }
    }

    /**
     * A malformed GEDCOM dataset inside an otherwise valid archive propagates the parse failure
     * rather than swallowing it. The reader also closes the archive on this path — since no handle
     * is returned to close it — but that release is not observable from here.
     */
    #[Test]
    public function propagatesAParseFailureFromAMalformedGedcomEntry(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'gdz');
        self::assertIsString($path);

        $archive = new ZipArchive();
        self::assertTrue($archive->open($path, ZipArchive::OVERWRITE));
        $archive->addFromString('gedcom.ged', "0 HEAD\n1 GEDC\n2 VERS 7.0\nINVALID LINE\n0 TRLR\n");
        $archive->close();

        try {
            $this->expectException(UnableToParseLineException::class);

            GedcomZipReader::openArchive($path);
        } finally {
            unlink($path);
        }
    }

    /**
     * The parsed document stays valid after the archive is closed, and closing is idempotent.
     */
    #[Test]
    public function keepsTheDocumentValidAfterCloseAndClosesIdempotently(): void
    {
        $archive  = GedcomZipReader::openArchive(__DIR__ . '/files/media.gdz');
        $document = $archive->getDocument();

        $archive->close();
        $archive->close();

        self::assertSame($document, $archive->getDocument(), 'the detached document survives close');
        self::assertCount(1, $archive->getDocument()->multimedia, 'the document still holds its one multimedia record after close');
    }

    /**
     * Resolving a media file from a closed archive fails loud rather than handing back a dead stream.
     */
    #[Test]
    public function rejectsMediaAccessAfterClose(): void
    {
        $archive = GedcomZipReader::openArchive(__DIR__ . '/files/media.gdz');
        $archive->close();

        $this->expectException(InvalidArchiveException::class);

        $archive->openFile('media/pixel.png');
    }
}
