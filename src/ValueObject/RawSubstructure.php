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
 * One entry is not itself an unconsumed substructure but a carrier for unconsumed ones: when a tag
 * the model types as a plain value (a scalar such as `AGNC`, or a pointer list such as `CHIL`)
 * nonetheless carries substructures, those descendants have nowhere to live on the typed property,
 * so they are preserved here beneath a carrier reproducing that tag's own line. Only a carrier's
 * {@see $children} are unconsumed; its {@see $value} and {@see $xref} repeat what the typed property
 * already holds, because that is what identifies the occurrence — where the property is a list, it
 * is the only thing tying a qualifier to the entry it qualifies.
 *
 * An entry may also be a line the model *does* type but could not attribute — one whose level skips
 * a step, which the grammar gives no enclosing structure at all. The container it lands on is then
 * the nearest preceding shallower line: a deliberate recovery rather than something the file stated,
 * and the one place this shape answers a question the grammar leaves open. {@see self::$level}
 * records the level the line was actually written at, which tells such an entry apart from one whose
 * tag is simply out of schema at that position and keeps the original line reconstructible.
 *
 * A continuation (`CONC`/`CONT`) reaches this list the same way when its level skips: it is a
 * pseudo-structure rather than a substructure, and one that could no longer be folded into the line
 * it meant to continue, so it is kept verbatim rather than dropped — but it is not a substructure of
 * the entry it sits under.
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
     * @param int|null              $level    The level the line was written at. A folded continuation is
     *                                        part of the line it continues, so it has no level of its own.
     */
    public function __construct(
        public string $tag,
        public ?string $value = null,
        public ?string $xref = null,
        public array $children = [],
        public ?int $level = null,
    ) {
    }
}
