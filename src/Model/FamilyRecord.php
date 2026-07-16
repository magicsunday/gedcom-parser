<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

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
        public array $uid = [],
        public array $exid = [],
        public ?CreationDate $crea = null,
        public ?ChangeDate $chan = null,
        public array $unknown = [],
    ) {
    }
}
