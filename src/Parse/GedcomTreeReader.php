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
 * memory as a whole. It is version-agnostic: it applies no tag or grammar knowledge and simply
 * mirrors the line structure, which the schema-driven mapping layer then interprets.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class GedcomTreeReader
{
    /**
     * @param Reader $reader The line reader to build the tree from
     */
    public function __construct(
        private Reader $reader,
    ) {
    }

    /**
     * Reads the next level-0 record and its complete substructure subtree.
     *
     * @return GedcomNode|null The next record node, or NULL at end of stream
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
     * @return GedcomNode The node for the current line
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

            $children[] = $this->buildCurrentNode();
        }

        return new GedcomNode($level, $tag, $identifier, $xref, $value, $children);
    }
}
