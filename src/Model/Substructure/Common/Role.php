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
 * A typed GEDCOM 7.0 role (`ROLE`) — the part an individual played in an event or association.
 *
 * The role is an enumerated value ({@see $value}, e.g. `CHIL`, `GODP`, `WITN`, `OTHER`), optionally
 * qualified by a free-text {@see $phrase} that describes an `OTHER` or otherwise-imprecise role. The
 * value is kept verbatim, so an extension or unlisted role survives rather than being rejected.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Role
{
    /**
     * @param string|null           $value   The enumerated role value (ROLE), preserved verbatim, or NULL when absent.
     * @param string|null           $phrase  The free-text phrase qualifying the role (PHRASE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $phrase = null,
        public array $unknown = [],
    ) {
    }
}
