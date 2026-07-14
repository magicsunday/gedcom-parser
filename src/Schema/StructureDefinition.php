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
 * The declarative definition of one GEDCOM structure, compiled from the machine-readable GEDCOM
 * registry.
 *
 * It names the structure's URI and GEDCOM tag, its payload (value) type, its optional
 * enumeration set, and the substructures it permits — the latter grouped by child tag so a
 * parsed child line can be resolved to its definition in the context of its parent.
 *
 * A single child tag may map to more than one candidate substructure: GEDCOM 5.5.1 splits the
 * classic "inline value or cross-reference pointer" tags (`NOTE`, `SOUR`, `OBJE`, `REPO`) into
 * two structure definitions that share one tag but differ by payload. Each tag therefore groups
 * a list of candidates; the mapping layer disambiguates by the parsed line's payload (pointer
 * versus text).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class StructureDefinition
{
    /**
     * @param string                            $uri            The URI identifying the structure
     * @param string                            $tag            The GEDCOM tag of the structure
     * @param string|null                       $payload        The payload (value) type token, or NULL when the structure has no value
     * @param string|null                       $enumerationSet The URI of the enumeration set constraining the payload, or NULL
     * @param array<string, list<Substructure>> $substructures  The permitted substructures, grouped by child tag
     */
    public function __construct(
        public string $uri,
        public string $tag,
        public ?string $payload,
        public ?string $enumerationSet,
        public array $substructures,
    ) {
    }

    /**
     * Returns the candidate substructures permitted under the given child tag, in registry
     * order, or an empty list when the tag is not a substructure of this structure.
     *
     * @param string $tag The child GEDCOM tag
     *
     * @return list<Substructure>
     */
    public function substructuresFor(string $tag): array
    {
        return $this->substructures[$tag] ?? [];
    }
}
