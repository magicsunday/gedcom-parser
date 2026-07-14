<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\TypedModel;

/**
 * A typed GEDCOM individual (INDI) record.
 *
 * A nested typed record: alongside its cross-reference identifier it exposes its events as typed
 * {@see EventDetail} objects, each carrying the typed value-object leaves the mapping layer builds
 * from the parsed tree. Each event is a list because GEDCOM 5.5.1 permits it to repeat ({0:M},
 * e.g. conflicting sources).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class IndividualRecord
{
    /**
     * @param string                   $xref The record cross-reference identifier
     * @param list<PersonalName>       $name The individual's names
     * @param string|null              $sex  The individual's sex, or NULL when absent
     * @param list<EventDetail>        $birt The birth events
     * @param list<EventDetail>        $deat The death events
     * @param list<EventDetail>        $buri The burial events
     * @param list<ChildToFamilyLink>  $famc The families in which the individual is a child
     * @param list<SpouseToFamilyLink> $fams The families in which the individual is a partner
     */
    public function __construct(
        public string $xref,
        public array $name = [],
        public ?string $sex = null,
        public array $birt = [],
        public array $deat = [],
        public array $buri = [],
        public array $famc = [],
        public array $fams = [],
    ) {
    }
}
