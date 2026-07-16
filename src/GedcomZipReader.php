<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Exception\InvalidArchiveException;
use MagicSunday\Gedcom\Exception\MissingGedcomEntryException;
use MagicSunday\Gedcom\Model\GedcomDocument;
use Psr\Http\Message\StreamInterface;
use Throwable;
use ZipArchive;

use function fclose;
use function fopen;
use function fwrite;
use function sprintf;
use function strlen;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * Reads a GEDZIP (`.gdz`) archive into the typed model.
 *
 * GEDZIP is the GEDCOM 7.0 ZIP container format: a ZIP archive carrying the dataset in a mandated
 * `gedcom.ged` entry alongside any embedded local media files. This reader locates that entry,
 * streams it into the ordinary schema-driven pipeline through {@see Parser}, and returns the typed
 * {@see GedcomDocument} — the container is a pure packaging concern, so the dataset inside is parsed
 * exactly as a plain `.ged` stream. Embedded media files are not resolved here.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class GedcomZipReader
{
    /**
     * The mandated archive entry name carrying the GEDCOM dataset (case-sensitive, per the GEDZIP
     * specification).
     */
    private const string GEDCOM_ENTRY = 'gedcom.ged';

    /**
     * The chunk size, in bytes, used to spool a stream input to a temporary file.
     */
    private const int SPOOL_CHUNK = 1 << 16;

    /**
     * Private constructor; use the static reader methods.
     */
    private function __construct()
    {
    }

    /**
     * Opens a GEDZIP archive from a file path, parsing its dataset and keeping the archive open for
     * embedded-media access. The caller owns the returned handle and must {@see GedcomArchive::close()}
     * it when done.
     *
     * @param string   $path     The path to the `.gdz` archive on disk.
     * @param int|null $maxBytes The maximum number of decompressed `gedcom.ged` bytes to read before
     *                           aborting with an {@see Exception\InputTooLargeException},
     *                           or NULL for {@see Reader::DEFAULT_MAX_BYTES}. The
     *                           cap bounds the inflated dataset, so it defeats a decompression bomb;
     *                           lower it when parsing untrusted archives.
     *
     * @return GedcomArchive The open archive handle exposing the parsed document and its media.
     *
     * @throws InvalidArchiveException     When the file cannot be opened as a ZIP archive.
     * @throws MissingGedcomEntryException When the archive lacks the mandated `gedcom.ged` entry.
     */
    public static function openArchive(string $path, ?int $maxBytes = null): GedcomArchive
    {
        $archive = new ZipArchive();
        $opened  = $archive->open($path);

        if ($opened !== true) {
            throw new InvalidArchiveException(
                sprintf('Unable to open GEDZIP archive "%s" (ZipArchive error code %d).', $path, $opened)
            );
        }

        $resource = $archive->getStream(self::GEDCOM_ENTRY);

        if ($resource === false) {
            $archive->close();

            throw new MissingGedcomEntryException(
                sprintf('The GEDZIP archive "%s" does not contain the mandated "%s" entry.', $path, self::GEDCOM_ENTRY)
            );
        }

        // The archive stays open in the returned handle for on-demand media access; parse() consumes
        // the whole gedcom.ged stream here, so the document is fully materialised before returning.
        // A parse failure must close the archive too, since no handle is returned to close it.
        try {
            $document = (new Parser((new StreamFactory())->createStreamFromResource($resource), $maxBytes))->parse();
        } catch (Throwable $throwable) {
            $archive->close();

            throw $throwable;
        }

        return new GedcomArchive($archive, $document);
    }

    /**
     * Reads a GEDZIP archive from a file path into the typed model, without keeping it open for
     * media access.
     *
     * @param string   $path     The path to the `.gdz` archive on disk.
     * @param int|null $maxBytes The maximum number of decompressed `gedcom.ged` bytes to read before
     *                           aborting, or NULL for the reader's default. Lower it when parsing
     *                           untrusted archives.
     *
     * @return GedcomDocument The parsed document, its records grouped by type.
     *
     * @throws InvalidArchiveException     When the file cannot be opened as a ZIP archive.
     * @throws MissingGedcomEntryException When the archive lacks the mandated `gedcom.ged` entry.
     */
    public static function readFile(string $path, ?int $maxBytes = null): GedcomDocument
    {
        $archive = self::openArchive($path, $maxBytes);

        try {
            return $archive->getDocument();
        } finally {
            $archive->close();
        }
    }

    /**
     * Reads a GEDZIP archive from a stream into the typed model. Because the ZIP facility requires a
     * seekable file, the stream is first spooled to a temporary file, which is removed afterwards.
     *
     * @param StreamInterface $stream   The `.gdz` archive stream.
     * @param int|null        $maxBytes The maximum number of decompressed `gedcom.ged` bytes to read
     *                                  before aborting, or NULL for the reader's default. The cap
     *                                  bounds the inflated dataset, not the compressed archive the
     *                                  stream is first spooled to disk as. Lower it when parsing
     *                                  untrusted archives.
     *
     * @return GedcomDocument The parsed document, its records grouped by type.
     *
     * @throws InvalidArchiveException     When the stream cannot be spooled or opened as a ZIP archive.
     * @throws MissingGedcomEntryException When the archive lacks the mandated `gedcom.ged` entry.
     */
    public static function read(StreamInterface $stream, ?int $maxBytes = null): GedcomDocument
    {
        $path = tempnam(sys_get_temp_dir(), 'gdz');

        if ($path === false) {
            throw new InvalidArchiveException('Unable to create a temporary file to spool the GEDZIP stream.');
        }

        try {
            self::spool($stream, $path);

            return self::readFile($path, $maxBytes);
        } finally {
            unlink($path);
        }
    }

    /**
     * Spools a stream to a file on disk in bounded chunks so a large archive is not held in memory.
     *
     * @param StreamInterface $stream The source stream.
     * @param string          $path   The destination file path.
     *
     * @return void
     *
     * @throws InvalidArchiveException When the destination file cannot be opened for writing.
     */
    private static function spool(StreamInterface $stream, string $path): void
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new InvalidArchiveException(sprintf('Unable to open the temporary file "%s" for writing.', $path));
        }

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        try {
            while (!$stream->eof()) {
                $chunk = $stream->read(self::SPOOL_CHUNK);

                if ($chunk === '') {
                    break;
                }

                if (fwrite($handle, $chunk) !== strlen($chunk)) {
                    throw new InvalidArchiveException(
                        sprintf('Unable to spool the GEDZIP stream to the temporary file "%s".', $path)
                    );
                }
            }
        } finally {
            fclose($handle);
        }
    }
}
