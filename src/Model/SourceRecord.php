<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Substructure\Common\UserReference;
use MagicSunday\Gedcom\Model\Substructure\Source\RepositoryCitation;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed GEDCOM source (SOUR) record.
 *
 * Describes a source of genealogical information — its title, author, publication facts and any
 * verbatim source text. Each descriptive field is a single optional text value ({0:1}); the
 * repositories that hold the source (REPO, both the inline and pointer variant) are typed
 * {@see RepositoryCitation}s carrying their call numbers.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SourceRecord
{
    /**
     * @param string                   $xref    The record cross-reference identifier.
     * @param string|null              $titl    The source title (TITL), or NULL when absent.
     * @param string|null              $auth    The source author (AUTH), or NULL when absent.
     * @param string|null              $publ    The publication facts (PUBL), or NULL when absent.
     * @param string|null              $abbr    The short abbreviated title (ABBR), or NULL when absent.
     * @param string|null              $text    The verbatim source text (TEXT), or NULL when absent.
     * @param list<Note>               $note    The record-level notes (NOTE).
     * @param list<string>             $snote   The GEDCOM 7.0 shared-note cross-reference pointers (SNOTE); empty when none.
     * @param list<UserReference>      $refn    The user reference numbers (REFN); empty when none.
     * @param list<string>             $uid     The GEDCOM 7.0 unique identifiers (UID); empty when none.
     * @param list<ExternalIdentifier> $exid    The GEDCOM 7.0 external identifiers (EXID); empty when none.
     * @param CreationDate|null        $crea    The GEDCOM 7.0 record creation timestamp (CREA), or NULL when absent.
     * @param ChangeDate|null          $chan    The record change timestamp (CHAN), or NULL when absent.
     * @param list<RepositoryCitation> $repo    The repositories holding the source (REPO); empty when none.
     * @param list<RawSubstructure>    $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public string $xref,
        public ?string $titl = null,
        public ?string $auth = null,
        public ?string $publ = null,
        public ?string $abbr = null,
        public ?string $text = null,
        // jscpd:ignore-start — the shared record-metadata constructor tail coincides with the sibling record's; identical boilerplate, not real duplication.
        public array $note = [],
        public array $snote = [],
        public array $refn = [],
        public array $uid = [],
        public array $exid = [],
        public ?CreationDate $crea = null,
        public ?ChangeDate $chan = null,
        public array $repo = [],
        public array $unknown = [],
        // jscpd:ignore-end
    ) {
    }
}
