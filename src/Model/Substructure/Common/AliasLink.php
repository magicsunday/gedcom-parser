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
 * A typed alias (`ALIA`) linking an individual record to another individual record that describes the
 * same person.
 *
 * The aliased individual is normally referenced by its cross-reference pointer ({@see $xref}, which
 * may be the GEDCOM 7.0 `@VOID@` placeholder). GEDCOM 5.5.1 carries the bare pointer alone, while
 * GEDCOM 7.0 additionally qualifies it with a free-text {@see $phrase}. Some GEDCOM 5.5.1 files misuse
 * the tag to carry a free-text alternate name instead of a pointer; that non-conformant text is
 * tolerated and preserved verbatim in {@see $value} so the alias is never dropped.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class AliasLink
{
    /**
     * @param string|null           $xref    The aliased individual's cross-reference pointer (or the GEDCOM 7.0 `@VOID@` placeholder), or NULL when the alias is a non-pointer text value.
     * @param string|null           $value   The non-pointer free-text alias (a tolerated GEDCOM 5.5.1 misuse carrying an alternate name), or NULL when the alias is a pointer.
     * @param string|null           $phrase  The GEDCOM 7.0 free-text phrase qualifying the alias pointer (PHRASE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $xref = null,
        public ?string $value = null,
        public ?string $phrase = null,
        public array $unknown = [],
    ) {
    }
}
