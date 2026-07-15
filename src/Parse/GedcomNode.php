<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parse;

/**
 * An immutable node in the generic GEDCOM parse tree.
 *
 * One node mirrors one GEDCOM line together with its nested substructures. It is the
 * version-agnostic intermediate representation the schema-driven mapping consumes: a line's
 * tag, its optional cross-reference identifier declared before the tag (`@X@`, normally only on
 * a level-0 record line), its optional cross-reference pointer (`@X@` in the line value), its
 * optional literal value, and its child lines.
 *
 * A line value is either a pointer or literal text, never both, so at most one of {@see $xref}
 * and {@see $value} is non-NULL.
 *
 * The original numeric level is retained even though the tree already encodes it as depth: for
 * malformed input a substructure may skip levels (a `parent+2` jump), so the raw level lets the
 * schema-driven mapping layer enforce the GEDCOM "substructure level is exactly parent+1" rule
 * that the version-agnostic tree builder deliberately does not.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class GedcomNode
{
    /**
     * @param int              $level      The original GEDCOM level number of the line.
     * @param string           $tag        The GEDCOM tag of the line.
     * @param string|null      $identifier The cross-reference identifier declared before the tag (`@X@`), normally only on a level-0 record line, or NULL.
     * @param string|null      $xref       The cross-reference pointer carried as the value, or NULL.
     * @param string|null      $value      The literal line value, or NULL when absent or a pointer.
     * @param list<GedcomNode> $children   The nested substructure nodes, in document order.
     */
    public function __construct(
        public int $level,
        public string $tag,
        public ?string $identifier,
        public ?string $xref,
        public ?string $value,
        public array $children = [],
    ) {
    }
}
