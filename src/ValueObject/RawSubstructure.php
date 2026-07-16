<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\ValueObject;

/**
 * A verbatim copy of a substructure the typed model did not consume — an extension tag (a
 * `_`-prefixed vendor tag such as `_WT_USER`), any out-of-place tag, and every schema-recognised
 * tag a record does not yet model as a typed property (e.g. a user reference `REFN`). The
 * mapper preserves these on the carrying object's `$unknown` list instead of dropping them; the
 * whole subtree (including the raw children's own children) is retained and can be walked.
 *
 * This is a Model-layer leaf and deliberately mirrors — but does not reference — the parse-layer
 * node, so the typed model stays independent of the parser internals.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class RawSubstructure
{
    /**
     * @param string                $tag      The raw GEDCOM tag (kept verbatim, including a leading `_`).
     * @param string|null           $value    The line value, or NULL when the line carries none.
     * @param string|null           $xref     The cross-reference pointer target, or NULL when it is not a pointer.
     * @param list<RawSubstructure> $children The nested substructures, preserved verbatim in document order.
     */
    public function __construct(
        public string $tag,
        public ?string $value = null,
        public ?string $xref = null,
        public array $children = [],
    ) {
    }
}
