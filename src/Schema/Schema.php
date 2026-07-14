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
     * @param array<string, StructureDefinition> $structures The structure definitions, keyed by URI
     */
    public function __construct(
        public array $structures,
    ) {
    }

    /**
     * Returns the structure definition for the given URI, or NULL when the version has no such
     * structure.
     *
     * @param string $uri The structure URI
     */
    public function byUri(string $uri): ?StructureDefinition
    {
        return $this->structures[$uri] ?? null;
    }
}
