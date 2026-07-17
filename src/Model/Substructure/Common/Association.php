<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Common;

use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed association (`ASSO`) linking a record to an individual by cross-reference.
 *
 * An association records a relationship to another individual — a godparent, a witness, an
 * employer — that is not captured by the family structures. The linked individual is referenced by
 * its cross-reference pointer (which may be the GEDCOM 7.0 `@VOID@` placeholder when the individual
 * is not present in the file).
 *
 * The relationship is described differently across versions and both forms are preserved: GEDCOM
 * 5.5.1 carries a free-text relationship in {@see $rela}, while GEDCOM 7.0 carries an enumerated
 * {@see $role} optionally qualified by a free-text {@see $phrase}. The association also carries its
 * own notes and source citations.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Association
{
    /**
     * @param string                $xref    The associated individual's cross-reference pointer (or the GEDCOM 7.0 `@VOID@` placeholder).
     * @param string|null           $rela    The GEDCOM 5.5.1 free-text relationship (RELA), or NULL when absent.
     * @param Role|null             $role    The GEDCOM 7.0 enumerated role (ROLE) with its optional phrase, or NULL when absent.
     * @param string|null           $phrase  The GEDCOM 7.0 free-text phrase qualifying the association pointer (PHRASE), or NULL when absent.
     * @param list<Note>            $note    The notes on the association (NOTE).
     * @param list<SourceCitation>  $sour    The source citations of the association (SOUR).
     * @param list<string>          $snote   The GEDCOM 7.0 shared-note cross-reference pointers (SNOTE); empty when none.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public string $xref,
        public ?string $rela = null,
        public ?Role $role = null,
        public ?string $phrase = null,
        public array $note = [],
        public array $sour = [],
        public array $snote = [],
        public array $unknown = [],
    ) {
    }
}
