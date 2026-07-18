<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Source;

use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A block of events a source records (`DATA`.`EVEN`) — which kinds of event the source covers, and
 * over what period and place it records them.
 *
 * The {@see $value} is the list of event types the block applies to, kept verbatim as written (a
 * comma-separated enumeration such as `BIRT, DEAT, MARR`), so an extension or unlisted type survives.
 * The {@see $date} is a date period bounding the records rather than a single event's date.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SourceDataEvent
{
    /**
     * @param string|null           $value   The list of event types the source records (EVEN), preserved verbatim, or NULL when absent.
     * @param DateValue|null        $date    The date period over which the events were recorded (DATE), or NULL when absent.
     * @param PlaceValue|null       $plac    The place the events were recorded for (PLAC), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?DateValue $date = null,
        public ?PlaceValue $plac = null,
        public array $unknown = [],
    ) {
    }
}
