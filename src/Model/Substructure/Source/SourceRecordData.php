<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Source;

use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * The data block of a source record (`DATA`) — what the source records, rather than what it is.
 *
 * It gathers the blocks of events the source covers ({@see $even}), the agency responsible for
 * keeping those records ({@see $agnc}) and any accompanying notes. This is the record-level block;
 * the transcribed text and date of an individual citation are carried by
 * {@see SourceCitationData} instead.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SourceRecordData
{
    /**
     * @param list<SourceDataEvent> $even    The blocks of events the source records (EVEN).
     * @param string|null           $value   The block's line value, which the schema does not permit; tolerated and preserved verbatim rather than dropped, or NULL as is normal.
     * @param string|null           $agnc    The agency responsible for the records (AGNC), or NULL when absent.
     * @param list<Note>            $note    The notes on the data block (NOTE).
     * @param list<string>          $snote   The GEDCOM 7.0 shared-note cross-reference pointers (SNOTE); empty when none.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public array $even = [],
        public ?string $value = null,
        public ?string $agnc = null,
        public array $note = [],
        public array $snote = [],
        public array $unknown = [],
    ) {
    }
}
