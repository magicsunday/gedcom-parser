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
 * A typed GEDCOM individual (INDI) record.
 *
 * A nested typed record: alongside its cross-reference identifier it exposes its life events as
 * typed {@see EventDetail} objects and its attributes as typed {@see AttributeDetail} objects, each
 * carrying the typed value-object leaves the mapping layer builds from the parsed tree. Each is a
 * list because GEDCOM permits the tag to repeat ({0:M}, e.g. conflicting sources or several
 * occupations).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class IndividualRecord
{
    /**
     * @param string                   $xref    The record cross-reference identifier.
     * @param list<PersonalName>       $name    The individual's names.
     * @param string|null              $sex     The individual's sex, or NULL when absent.
     * @param list<EventDetail>        $birt    The birth events.
     * @param list<EventDetail>        $deat    The death events.
     * @param list<EventDetail>        $buri    The burial events.
     * @param list<EventDetail>        $bapm    The baptism events (BAPM).
     * @param list<EventDetail>        $barm    The bar-mitzvah events (BARM).
     * @param list<EventDetail>        $basm    The bas-mitzvah events (BASM).
     * @param list<EventDetail>        $bles    The blessing events (BLES).
     * @param list<EventDetail>        $chr     The christening events (CHR).
     * @param list<EventDetail>        $chra    The adult-christening events (CHRA).
     * @param list<EventDetail>        $conf    The confirmation events (CONF).
     * @param list<EventDetail>        $crem    The cremation events (CREM).
     * @param list<EventDetail>        $emig    The emigration events (EMIG).
     * @param list<EventDetail>        $fcom    The first-communion events (FCOM).
     * @param list<EventDetail>        $grad    The graduation events (GRAD).
     * @param list<EventDetail>        $immi    The immigration events (IMMI).
     * @param list<EventDetail>        $natu    The naturalization events (NATU).
     * @param list<EventDetail>        $ordn    The ordination events (ORDN).
     * @param list<EventDetail>        $prob    The probate events (PROB).
     * @param list<EventDetail>        $reti    The retirement events (RETI).
     * @param list<EventDetail>        $will    The will events (WILL).
     * @param list<EventDetail>        $adop    The adoption events (ADOP).
     * @param list<EventDetail>        $cens    The census events (CENS).
     * @param list<AttributeDetail>    $occu    The occupations (OCCU).
     * @param list<AttributeDetail>    $resi    The residences (RESI).
     * @param list<AttributeDetail>    $educ    The education attributes (EDUC).
     * @param list<AttributeDetail>    $reli    The religious affiliations (RELI).
     * @param list<AttributeDetail>    $cast    The caste attributes (CAST).
     * @param list<AttributeDetail>    $dscr    The physical descriptions (DSCR).
     * @param list<AttributeDetail>    $idno    The identifying numbers (IDNO).
     * @param list<AttributeDetail>    $nati    The nationalities (NATI).
     * @param list<AttributeDetail>    $nchi    The child-count attributes (NCHI).
     * @param list<AttributeDetail>    $nmr     The marriage-count attributes (NMR).
     * @param list<AttributeDetail>    $prop    The property attributes (PROP).
     * @param list<AttributeDetail>    $ssn     The social-security numbers (SSN).
     * @param list<AttributeDetail>    $titl    The nobility titles (TITL).
     * @param list<AttributeDetail>    $fact    The generic facts (FACT).
     * @param list<ChildToFamilyLink>  $famc    The families in which the individual is a child.
     * @param list<SpouseToFamilyLink> $fams    The families in which the individual is a partner.
     * @param list<string>             $subm    The submitter cross-reference pointers (SUBM); empty when none.
     * @param list<string>             $anci    The ancestor-interest submitter cross-reference pointers (ANCI); empty when none.
     * @param list<string>             $desi    The descendant-interest submitter cross-reference pointers (DESI); empty when none.
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
        public array $name = [],
        public ?string $sex = null,
        public array $birt = [],
        public array $deat = [],
        public array $buri = [],
        public array $bapm = [],
        public array $barm = [],
        public array $basm = [],
        public array $bles = [],
        public array $chr = [],
        public array $chra = [],
        public array $conf = [],
        public array $crem = [],
        public array $emig = [],
        public array $fcom = [],
        public array $grad = [],
        public array $immi = [],
        public array $natu = [],
        public array $ordn = [],
        public array $prob = [],
        public array $reti = [],
        public array $will = [],
        public array $adop = [],
        public array $cens = [],
        public array $occu = [],
        public array $resi = [],
        public array $educ = [],
        public array $reli = [],
        public array $cast = [],
        public array $dscr = [],
        public array $idno = [],
        public array $nati = [],
        public array $nchi = [],
        public array $nmr = [],
        public array $prop = [],
        public array $ssn = [],
        public array $titl = [],
        public array $fact = [],
        public array $famc = [],
        public array $fams = [],
        public array $subm = [],
        public array $anci = [],
        public array $desi = [],
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
