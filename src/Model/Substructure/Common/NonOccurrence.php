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
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed GEDCOM 7.0 non-occurrence (`NO`) — an assertion that a specific kind of event did NOT
 * happen.
 *
 * The {@see $value} names the event type that did not occur (an enumerated value such as `MARR` or
 * `NATU`, kept verbatim so an extension survives). The optional {@see $date} is the date period
 * during which the absence is asserted, and the assertion carries its own notes and source citations
 * as evidence.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class NonOccurrence
{
    /**
     * @param string|null           $value   The enumerated event type that did not occur (NO), preserved verbatim, or NULL when absent.
     * @param DateValue|null        $date    The date period during which the event did not occur (DATE), or NULL when absent.
     * @param list<Note>            $note    The notes on the non-occurrence (NOTE).
     * @param list<string>          $snote   The shared-note cross-reference pointers (SNOTE); empty when none.
     * @param list<SourceCitation>  $sour    The source citations of the non-occurrence (SOUR).
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?DateValue $date = null,
        public array $note = [],
        public array $snote = [],
        public array $sour = [],
        public array $unknown = [],
    ) {
    }
}
