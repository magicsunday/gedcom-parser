<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parse;

use MagicSunday\Gedcom\Reader;

/**
 * Builds a generic {@see GedcomNode} tree from the flat lines a {@see Reader} yields.
 *
 * The reader exposes GEDCOM as a stream of level-tagged lines; this class nests them into a
 * tree by their level numbers, one level-0 record at a time so the source is never held in
 * memory as a whole. It applies no structural grammar knowledge and simply mirrors the line
 * structure, which the schema-driven mapping layer then interprets — the one exception being the
 * `CONC`/`CONT` continuation lines, which are a physical line-length serialization of a single
 * logical value (`CONT` in both GEDCOM versions, `CONC` in 5.5.1 only — 7.0 keeps only `CONT`)
 * and are reassembled into their superstructure's value rather than exposed as child nodes. Only a
 * genuine text value is continued: a continuation on a pointer line is left as a child for the
 * mapping layer to reject, preserving the node invariant that a value and a pointer are exclusive.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class GedcomTreeReader
{
    /**
     * The continuation tag that appends its payload to the superstructure value without a break.
     */
    private const string TAG_CONC = 'CONC';

    /**
     * The continuation tag that appends its payload to the superstructure value after a newline.
     */
    private const string TAG_CONT = 'CONT';

    /**
     * @param Reader $reader The line reader to build the tree from.
     */
    public function __construct(
        private Reader $reader,
    ) {
    }

    /**
     * Reads the next level-0 record and its complete substructure subtree.
     *
     * @return GedcomNode|null The next record node, or NULL at end of stream.
     */
    public function readRecord(): ?GedcomNode
    {
        if (!$this->reader->read()) {
            return null;
        }

        return $this->buildCurrentNode();
    }

    /**
     * Builds the node for the line the reader currently sits on, recursively consuming every
     * deeper line as a child and putting back the first line that is not part of this subtree.
     *
     * @return GedcomNode The node for the current line.
     */
    private function buildCurrentNode(): GedcomNode
    {
        $level      = $this->reader->level();
        $tag        = $this->reader->tag();
        $identifier = $this->reader->identifier();
        $xref       = $this->reader->xref();
        $value      = $this->reader->value();

        $children = [];

        while ($this->reader->read()) {
            if ($this->reader->level() <= $level) {
                $this->reader->back();

                break;
            }

            // A CONC/CONT line one level below this node continues its value across a physical
            // line break rather than forming a substructure: CONC appends without a separator,
            // CONT with a newline. Fold it into the value instead of nesting it as a child — but
            // only for a genuine text value; a continuation on a pointer line ($xref set) is
            // malformed and is left as a child so a value never coexists with a pointer.
            $childTag = $this->reader->tag();

            if (
                ($xref === null)
                && ($this->reader->level() === ($level + 1))
                && (($childTag === self::TAG_CONC) || ($childTag === self::TAG_CONT))
            ) {
                $separator = $childTag === self::TAG_CONT ? "\n" : '';
                $value     = ($value ?? '') . $separator . ($this->reader->value() ?? '');

                continue;
            }

            $children[] = $this->buildCurrentNode();
        }

        return new GedcomNode($level, $tag, $identifier, $xref, $value, $children);
    }
}
