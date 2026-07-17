<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Common;

use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed user reference number (`REFN`) — a record identifier assigned by the submitter's own
 * system rather than by GEDCOM.
 *
 * The reference itself is a free-text string ({@see $value}); an optional {@see $type} names the
 * external system or scheme the reference belongs to (the `REFN`.`TYPE` substructure), so several
 * references on one record stay distinguishable.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class UserReference
{
    /**
     * @param string|null           $value   The user reference number (REFN), preserved verbatim, or NULL when absent.
     * @param string|null           $type    The reference's type or originating system (REFN.TYPE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $type = null,
        public array $unknown = [],
    ) {
    }
}
