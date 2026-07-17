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
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed LDS ordinance — the shared shape of the individual ordinances (`BAPL`, `CONL`, `ENDL`,
 * `INIL`, `SLGC`) and the spouse sealing (`SLGS`).
 *
 * An ordinance records a religious rite performed in an LDS temple: when ({@see $date}), where
 * ({@see $temp} temple and {@see $plac} place) and with what completion {@see $stat status}, together
 * with the supporting notes and source citations. A child-to-parents sealing (`SLGC`) additionally
 * references the sealed-to family by cross-reference in {@see $famc}.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class LdsOrdinance
{
    /**
     * @param DateValue|null        $date    The date the ordinance was performed (DATE), or NULL when absent.
     * @param string|null           $temp    The temple where the ordinance was performed (TEMP), or NULL when absent.
     * @param PlaceValue|null       $plac    The place of the ordinance (PLAC), or NULL when absent.
     * @param OrdinanceStatus|null  $stat    The completion status of the ordinance (STAT), or NULL when absent.
     * @param string|null           $famc    The sealed-to family cross-reference pointer (FAMC, child-to-parents sealing only), or NULL when absent.
     * @param list<Note>            $note    The notes on the ordinance (NOTE).
     * @param list<string>          $snote   The shared-note cross-reference pointers (SNOTE); empty when none.
     * @param list<SourceCitation>  $sour    The source citations of the ordinance (SOUR).
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?DateValue $date = null,
        public ?string $temp = null,
        public ?PlaceValue $plac = null,
        public ?OrdinanceStatus $stat = null,
        public ?string $famc = null,
        public array $note = [],
        public array $snote = [],
        public array $sour = [],
        public array $unknown = [],
    ) {
    }
}
