<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Source;

use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\SourceRecord;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed source citation (`SOUR` under an event).
 *
 * A pointer citation references a {@see SourceRecord} by cross-reference; its {@see source()}
 * accessor resolves that reference lazily through the {@see GedcomDocument} index, so the citation
 * stays an immutable leaf holding only the raw pointer and no back-reference into the document
 * (a duplicate source cross-reference, which GEDCOM forbids, resolves to the last such record).
 *
 * This increment models the pointer citation's cross-reference, `PAGE`, `QUAY` and inline notes.
 * The citation's `DATA`/`EVEN` substructures, the GEDCOM 5.5.1 inline source-description text, and
 * source citations carried by structures other than an event are not yet modelled: as
 * schema-recognised-but-unmodelled substructures they are currently dropped (deferred to a later
 * increment of the typed-model roll-out), NOT preserved on {@see $unknown} — which captures only
 * tags the schema does not permit at their position.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SourceCitation
{
    /**
     * @param string|null           $xref    The cited source record's cross-reference, or NULL when the citation is not a pointer.
     * @param string|null           $page    The location within the source (PAGE), or NULL when absent.
     * @param string|null           $quay    The certainty assessment (QUAY), or NULL when absent.
     * @param list<Note>            $note    The inline notes on the citation.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-place tags), preserved verbatim.
     */
    public function __construct(
        public ?string $xref = null,
        public ?string $page = null,
        public ?string $quay = null,
        public array $note = [],
        public array $unknown = [],
    ) {
    }

    /**
     * Resolves the cited source record through the document's cross-reference index.
     *
     * @param GedcomDocument $document The document whose sources to resolve the citation against.
     *
     * @return SourceRecord|null The referenced source record, or NULL when the citation carries no
     *                           pointer or the document has no matching source.
     */
    public function source(GedcomDocument $document): ?SourceRecord
    {
        if ($this->xref === null) {
            return null;
        }

        return $document->source($this->xref);
    }
}
