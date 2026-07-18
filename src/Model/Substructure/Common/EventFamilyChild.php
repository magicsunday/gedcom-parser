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
 * A typed family-child pointer (`FAMC`) on a birth, christening or adoption event — the family the
 * child belongs to by way of that event.
 *
 * A birth or christening carries the bare pointer alone. An adoption additionally qualifies it with
 * {@see $adop}, naming which parent of the pointed-to family adopted the child. Should a file carry
 * free text instead of a pointer, that non-conformant value is tolerated and preserved in
 * {@see $value} rather than dropped.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class EventFamilyChild
{
    /**
     * @param string|null           $xref    The pointed-to family's cross-reference pointer (or the GEDCOM 7.0 `@VOID@` placeholder), or NULL when the payload is a non-pointer text value.
     * @param AdoptingParent|null   $adop    The parent of that family who adopted the child on an adoption event (ADOP), or NULL when absent.
     * @param string|null           $value   The non-pointer free-text value (a tolerated malformed pointer), or NULL when the payload is a family pointer.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $xref = null,
        public ?AdoptingParent $adop = null,
        public ?string $value = null,
        public array $unknown = [],
    ) {
    }
}
