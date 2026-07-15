<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\TypedModel;

use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;

/**
 * The typed detail shared by GEDCOM events: when and where the event took place, and the
 * individual's age at it.
 *
 * Each is exposed as its typed value object — {@see DateValue}, {@see PlaceValue},
 * {@see AgeValue} — parsed from the event's `DATE` / `PLAC` / `AGE` substructures by the mapping
 * layer's custom type handlers.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class EventDetail
{
    /**
     * @param DateValue|null  $date The date the event took place, or NULL when absent.
     * @param PlaceValue|null $plac The place the event took place, or NULL when absent.
     * @param AgeValue|null   $age  The individual's age at the event, or NULL when absent.
     */
    public function __construct(
        public ?DateValue $date = null,
        public ?PlaceValue $plac = null,
        public ?AgeValue $age = null,
    ) {
    }
}
