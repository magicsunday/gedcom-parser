<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\TypedModel;

use MagicSunday\Gedcom\ValueObject\DateValue;

/**
 * The typed detail shared by GEDCOM events: when the event took place.
 *
 * The date is exposed as the typed {@see DateValue} value object, parsed from the event's `DATE`
 * substructure by the mapping layer's custom type handler.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class EventDetail
{
    /**
     * @param DateValue|null $date The date the event took place, or NULL when absent
     */
    public function __construct(
        public ?DateValue $date = null,
    ) {
    }
}
