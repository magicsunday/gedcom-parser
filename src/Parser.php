<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Mapping\GedcomDocumentReader;
use MagicSunday\Gedcom\Model\FamilyRecord;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\IndividualRecord;
use MagicSunday\Gedcom\Model\MultimediaRecord;
use MagicSunday\Gedcom\Model\NoteRecord;
use MagicSunday\Gedcom\Model\RepositoryRecord;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\Model\SubmitterRecord;
use Psr\Http\Message\StreamInterface;

/**
 * A GEDCOM parser producing the typed model.
 *
 * Reads a GEDCOM stream through the schema-driven pipeline and returns a typed {@see GedcomDocument}
 * aggregate, detecting the document's version from its own header. The standard level-0 records
 * (INDI, FAM, SOUR, NOTE, REPO, OBJE, SUBM) are mapped onto their typed records.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Parser
{
    /**
     * The data-bearing level-0 record tags mapped onto their typed record classes. GEDCOM 7.0
     * renames the shared-note record from 5.5.1's `NOTE` to `SNOTE`; both map to the same
     * {@see NoteRecord}. This map is version-agnostic — the reader maps a level-0 tag only when the
     * detected version's schema actually defines it as a record, so a 5.5.1 document resolves its
     * `NOTE` record and a 7.0 document its `SNOTE` record, while the other (cross-version) tag,
     * absent from that version's schema, is skipped rather than mapped.
     *
     * @var array<string, class-string>
     */
    private const array RECORD_CLASSES = [
        'INDI'  => IndividualRecord::class,
        'FAM'   => FamilyRecord::class,
        'SOUR'  => SourceRecord::class,
        'NOTE'  => NoteRecord::class,
        'SNOTE' => NoteRecord::class,
        'REPO'  => RepositoryRecord::class,
        'OBJE'  => MultimediaRecord::class,
        'SUBM'  => SubmitterRecord::class,
    ];

    /**
     * @param StreamInterface $stream   The GEDCOM stream to parse.
     * @param int|null        $maxBytes The maximum number of bytes to read before aborting with an
     *                                  {@see Exception\InputTooLargeException},
     *                                  or NULL for {@see Reader::DEFAULT_MAX_BYTES}. Lower it when
     *                                  parsing untrusted input.
     */
    public function __construct(
        private StreamInterface $stream,
        private ?int $maxBytes = null,
    ) {
    }

    /**
     * Parses the GEDCOM stream into a typed aggregate.
     *
     * @return GedcomDocument The parsed document, its records grouped by type.
     */
    public function parse(): GedcomDocument
    {
        return GedcomDocumentReader::create(self::RECORD_CLASSES)->read($this->stream, $this->maxBytes);
    }
}
