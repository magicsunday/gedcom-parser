<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Substructure\Common\Address;
use MagicSunday\Gedcom\Model\Substructure\Common\UserReference;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed GEDCOM submitter (SUBM) record.
 *
 * This is the first record of the schema-driven typed model: an immutable value object whose
 * properties are the submitter's cross-reference identifier and its substructures, built by the
 * mapping layer from a parsed tree via the registry schema.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SubmitterRecord
{
    /**
     * @param string                   $xref    The record cross-reference identifier.
     * @param string|null              $name    The submitter's name, or NULL when the record carries none.
     * @param list<string>             $phon    The submitter's phone numbers.
     * @param Address|null             $addr    The submitter's postal address (ADDR), or NULL when absent.
     * @param list<string>             $email   The submitter's email addresses (EMAIL); empty when none.
     * @param list<string>             $fax     The submitter's fax numbers (FAX); empty when none.
     * @param list<string>             $www     The submitter's web pages (WWW); empty when none.
     * @param list<Note>               $note    The record-level notes (NOTE).
     * @param list<string>             $snote   The GEDCOM 7.0 shared-note cross-reference pointers (SNOTE); empty when none.
     * @param list<UserReference>      $refn    The user reference numbers (REFN); empty when none.
     * @param list<string>             $uid     The GEDCOM 7.0 unique identifiers (UID); empty when none.
     * @param list<ExternalIdentifier> $exid    The GEDCOM 7.0 external identifiers (EXID); empty when none.
     * @param CreationDate|null        $crea    The GEDCOM 7.0 record creation timestamp (CREA), or NULL when absent.
     * @param ChangeDate|null          $chan    The record change timestamp (CHAN), or NULL when absent.
     * @param list<RawSubstructure>    $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public string $xref,
        public ?string $name = null,
        public array $phon = [],
        public ?Address $addr = null,
        public array $email = [],
        public array $fax = [],
        public array $www = [],
        public array $note = [],
        public array $snote = [],
        public array $refn = [],
        public array $uid = [],
        public array $exid = [],
        public ?CreationDate $crea = null,
        public ?ChangeDate $chan = null,
        public array $unknown = [],
    ) {
    }
}
