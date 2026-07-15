<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

/**
 * An eager, typed aggregate of a parsed GEDCOM document.
 *
 * Where {@see \MagicSunday\Gedcom\Mapping\TypedGedcomParser} streams one typed record at a time,
 * this holds the whole document in memory with the records grouped by their modelled type, so a
 * consumer can reach every individual, family, source, note, repository or multimedia record
 * without re-iterating. A record whose type is not one of the modelled records is kept in
 * {@see $others} so nothing is dropped.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class GedcomDocument
{
    /**
     * @param list<IndividualRecord>      $individuals   The individual (INDI) records.
     * @param list<FamilyRecord>          $families      The family (FAM) records.
     * @param list<SourceRecord>          $sources       The source (SOUR) records.
     * @param list<NoteRecord>            $notes         The shared-note (NOTE) records.
     * @param list<RepositoryRecord>      $repositories  The repository (REPO) records.
     * @param list<MultimediaRecord>      $multimedia    The multimedia (OBJE) records.
     * @param list<SubmitterRecord>       $submitters    The submitter (SUBM) records.
     * @param list<object>                $others        Records whose type is not one of the modelled records.
     * @param array<string, list<string>> $extensionTags The GEDCOM 7.0 header-declared extension tags
     *                                                   mapped to their documented URIs (HEAD.SCHMA.TAG);
     *                                                   a tag may declare more than one URI. Empty for a
     *                                                   5.5.1 document.
     */
    public function __construct(
        public array $individuals = [],
        public array $families = [],
        public array $sources = [],
        public array $notes = [],
        public array $repositories = [],
        public array $multimedia = [],
        public array $submitters = [],
        public array $others = [],
        public array $extensionTags = [],
    ) {
    }

    /**
     * Drains a stream of typed records — as produced by
     * {@see \MagicSunday\Gedcom\Mapping\TypedGedcomParser::parse()} — into the aggregate, grouping
     * each record by its modelled type and preserving document order within each group.
     *
     * @param iterable<object>            $records       The typed records to aggregate.
     * @param array<string, list<string>> $extensionTags The header-declared extension tags mapped to their URIs.
     *
     * @return self The populated aggregate.
     */
    public static function fromRecords(iterable $records, array $extensionTags = []): self
    {
        $individuals  = [];
        $families     = [];
        $sources      = [];
        $notes        = [];
        $repositories = [];
        $multimedia   = [];
        $submitters   = [];
        $others       = [];

        foreach ($records as $record) {
            if ($record instanceof IndividualRecord) {
                $individuals[] = $record;
            } elseif ($record instanceof FamilyRecord) {
                $families[] = $record;
            } elseif ($record instanceof SourceRecord) {
                $sources[] = $record;
            } elseif ($record instanceof NoteRecord) {
                $notes[] = $record;
            } elseif ($record instanceof RepositoryRecord) {
                $repositories[] = $record;
            } elseif ($record instanceof MultimediaRecord) {
                $multimedia[] = $record;
            } elseif ($record instanceof SubmitterRecord) {
                $submitters[] = $record;
            } else {
                $others[] = $record;
            }
        }

        return new self(
            $individuals,
            $families,
            $sources,
            $notes,
            $repositories,
            $multimedia,
            $submitters,
            $others,
            $extensionTags,
        );
    }
}
