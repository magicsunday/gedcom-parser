<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Schema;

/**
 * The compiled declarative schema for one GEDCOM version: every structure definition of that
 * version, indexed by URI so a structure — and, through its substructures, its children — can be
 * resolved while mapping a parsed tree onto the typed model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Schema
{
    /**
     * @param array<string, StructureDefinition> $structures   The structure definitions, keyed by URI.
     * @param array<string, StructureDefinition> $recordsByTag The top-level record definitions, keyed by tag.
     */
    public function __construct(
        public array $structures,
        public array $recordsByTag = [],
    ) {
    }

    /**
     * Returns the structure definition for the given URI, or NULL when the version has no such
     * structure.
     *
     * @param string $uri The structure URI.
     */
    public function byUri(string $uri): ?StructureDefinition
    {
        return $this->structures[$uri] ?? null;
    }

    /**
     * Returns the top-level record definition for the given tag (e.g. `INDI`), or NULL when the
     * tag is not a record in this version.
     *
     * @param string $tag The record tag.
     */
    public function recordByTag(string $tag): ?StructureDefinition
    {
        return $this->recordsByTag[$tag] ?? null;
    }

    /**
     * Whether the given tag is a top-level record in this version's schema.
     *
     * @param string $tag The record tag.
     *
     * @return bool TRUE when the tag names a top-level record.
     */
    public function definesRecord(string $tag): bool
    {
        return isset($this->recordsByTag[$tag]);
    }
}
