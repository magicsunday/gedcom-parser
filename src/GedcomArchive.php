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
use MagicSunday\Gedcom\Model\GedcomDocument;
use Psr\Http\Message\StreamInterface;
use ZipArchive;

/**
 * An open GEDZIP (`.gdz`) archive: the parsed document plus on-demand access to its embedded media.
 *
 * Obtain one from {@see GedcomZipReader::openArchive()}. Unlike parsing the document, resolving an
 * embedded file needs the underlying ZIP archive to stay open, so the handle owns it. The caller
 * must {@see close()} the handle when done — ideally in a `finally` block — since a media stream
 * returned by {@see openFile()} reads lazily from the archive and is only valid while the handle is
 * open; a stream that must outlive the handle should be copied first. The parsed document, in
 * contrast, is a detached in-memory tree and stays valid after {@see close()}.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class GedcomArchive
{
    /**
     * Whether the underlying archive is still open.
     */
    private bool $open = true;

    /**
     * @param ZipArchive     $archive  The open ZIP archive backing the embedded-media access.
     * @param GedcomDocument $document The parsed document read from the archive's `gedcom.ged` entry.
     *
     * @internal Construct through {@see GedcomZipReader::openArchive()}.
     */
    public function __construct(
        private readonly ZipArchive $archive,
        private readonly GedcomDocument $document,
    ) {
    }

    /**
     * Returns the parsed document. It stays valid after {@see close()}.
     *
     * @return GedcomDocument The parsed document, its records grouped by type.
     */
    public function getDocument(): GedcomDocument
    {
        return $this->document;
    }

    /**
     * Resolves an `OBJE.FILE` payload to a readable stream over the embedded archive entry it
     * addresses, or NULL when the payload does not name an embedded file (a web or `file:` URL, an
     * absolute or traversing path) or names one the archive does not contain (a dangling reference).
     *
     * The returned stream reads lazily from the archive and is only valid while this handle is open.
     *
     * @param string $filePath The `OBJE.FILE` line value (a GEDCOM 7.0 FilePath).
     *
     * @return StreamInterface|null The embedded file's stream, or NULL when it is not resolvable.
     *
     * @throws InvalidArchiveException When the handle has already been closed.
     */
    public function openFile(string $filePath): ?StreamInterface
    {
        if (!$this->open) {
            throw new InvalidArchiveException('Cannot open a file from a closed GEDZIP archive.');
        }

        $entry = FilePath::toArchiveEntry($filePath);

        if ($entry === null) {
            return null;
        }

        $resource = $this->archive->getStream($entry);

        if ($resource === false) {
            return null;
        }

        return (new StreamFactory())->createStreamFromResource($resource);
    }

    /**
     * Closes the underlying archive. Idempotent, and safe to call from a `finally` block. Media
     * streams returned by {@see openFile()} must not be read after this call.
     *
     * @return void
     */
    public function close(): void
    {
        if (!$this->open) {
            return;
        }

        $this->archive->close();
        $this->open = false;
    }
}
