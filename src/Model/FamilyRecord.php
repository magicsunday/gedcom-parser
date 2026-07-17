<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed GEDCOM family (FAM) record.
 *
 * A family links the two partners and their children by cross-reference pointer, and carries its
 * shared events as typed {@see EventDetail} objects. Husband and wife are single pointers ({0:1}),
 * while children and marriage events repeat ({0:M}).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class FamilyRecord
{
    /**
     * @param string                   $xref    The record cross-reference identifier.
     * @param string|null              $husb    The husband's individual cross-reference pointer, or NULL.
     * @param string|null              $wife    The wife's individual cross-reference pointer, or NULL.
     * @param list<string>             $chil    The children's individual cross-reference pointers.
     * @param list<EventDetail>        $marr    The marriage events.
     * @param list<EventDetail>        $anul    The annulment events (ANUL).
     * @param list<EventDetail>        $cens    The census events (CENS).
     * @param list<EventDetail>        $div     The divorce events (DIV).
     * @param list<EventDetail>        $divf    The divorce-filed events (DIVF).
     * @param list<EventDetail>        $enga    The engagement events (ENGA).
     * @param list<EventDetail>        $marb    The marriage-banns events (MARB).
     * @param list<EventDetail>        $marc    The marriage-contract events (MARC).
     * @param list<EventDetail>        $marl    The marriage-licence events (MARL).
     * @param list<EventDetail>        $mars    The marriage-settlement events (MARS).
     * @param list<AttributeDetail>    $resi    The residences (RESI).
     * @param list<Note>               $note    The record-level notes (NOTE).
     * @param list<SourceCitation>     $sour    The record-level source citations (SOUR).
     * @param list<string>             $uid     The GEDCOM 7.0 unique identifiers (UID); empty when none.
     * @param list<ExternalIdentifier> $exid    The GEDCOM 7.0 external identifiers (EXID); empty when none.
     * @param CreationDate|null        $crea    The GEDCOM 7.0 record creation timestamp (CREA), or NULL when absent.
     * @param ChangeDate|null          $chan    The record change timestamp (CHAN), or NULL when absent.
     * @param list<RawSubstructure>    $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public string $xref,
        public ?string $husb = null,
        public ?string $wife = null,
        public array $chil = [],
        public array $marr = [],
        public array $anul = [],
        public array $cens = [],
        public array $div = [],
        public array $divf = [],
        public array $enga = [],
        public array $marb = [],
        public array $marc = [],
        public array $marl = [],
        public array $mars = [],
        public array $resi = [],
        public array $note = [],
        public array $sour = [],
        public array $uid = [],
        public array $exid = [],
        public ?CreationDate $crea = null,
        public ?ChangeDate $chan = null,
        public array $unknown = [],
    ) {
    }
}
