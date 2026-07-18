<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Common;

use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * The age of one partner at a family event or family attribute (`HUSB` or `WIFE`).
 *
 * These are the two substructures a family's events and attributes have beyond an individual's. Each
 * is a container the schema gives no value of its own; its sole purpose is to carry the {@see $age}
 * of that partner, which is exposed as the same typed {@see AgeValue} the rest of the model uses.
 *
 * The container has the same shape in both GEDCOM versions, though not the same reach: GEDCOM 7.0
 * permits it on more structures than GEDCOM 5.5.1 does. The schema requires the age, but a file that
 * omits it yields the container with no age rather than being rejected.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SpouseAge
{
    /**
     * @param AgeValue|null         $age     The partner's age at the event (AGE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?AgeValue $age = null,
        public array $unknown = [],
    ) {
    }
}
