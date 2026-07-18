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
 * A typed adopting parent (`ADOP`) beneath an adoption event's family-child pointer — which parent of
 * the pointed-to family adopted the child.
 *
 * The parent is an enumerated value ({@see $value}: `HUSB`, `WIFE` or `BOTH`), which GEDCOM 7.0
 * optionally qualifies by a free-text {@see $phrase}. The value is kept verbatim, so an extension or
 * unlisted value survives rather than being rejected.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class AdoptingParent
{
    /**
     * @param string|null           $value   The enumerated adopting parent (ADOP) — `HUSB`, `WIFE` or `BOTH` — preserved verbatim, or NULL when absent.
     * @param string|null           $phrase  The GEDCOM 7.0 free-text phrase qualifying the adopting parent (PHRASE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $phrase = null,
        public array $unknown = [],
    ) {
    }
}
