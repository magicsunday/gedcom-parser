<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\Schema\StructureDefinition;

/**
 * The manifest of registry structures that are generated into committed model classes. It is the
 * single source of truth shared by the generation driver and the freshness gate: the driver writes
 * each target's class, and the freshness test re-generates each and asserts it still matches the
 * committed file, so a committed generated class can never drift from the registry + generator.
 *
 * The roll-out grows this list, structure by structure, towards full coverage.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class GeneratedModels
{
    /**
     * Private constructor; this is a static-only manifest.
     */
    private function __construct()
    {
    }

    /**
     * The generation targets.
     *
     * @return list<array{version: GedcomVersion, uri: string, class: string, isRecord: bool, description: string}>
     */
    public static function targets(): array
    {
        return [
            [
                'version'     => GedcomVersion::V551,
                'uri'         => 'https://gedcom.io/terms/v5.5.1/SOUR-XREF_SOUR-DATA',
                'class'       => 'SourceCitationData',
                'isRecord'    => false,
                'description' => 'The data (transcribed text and its date) of a source citation.',
            ],
            [
                'version'     => GedcomVersion::V551,
                'uri'         => 'https://gedcom.io/terms/v5.5.1/SOUR-XREF_SOUR-EVEN',
                'class'       => 'SourceCitationEvent',
                'isRecord'    => false,
                'description' => 'The cited event of a source citation: its type and the informant role.',
            ],
            [
                'version'     => GedcomVersion::V551,
                'uri'         => 'https://gedcom.io/terms/v5.5.1/CALN',
                'class'       => 'CallNumber',
                'isRecord'    => false,
                'description' => 'A repository call number: the identifier a repository files an item under, and its media type.',
            ],
            [
                'version'     => GedcomVersion::V551,
                'uri'         => 'https://gedcom.io/terms/v5.5.1/REPO-XREF_REPO',
                'class'       => 'RepositoryCitation',
                'isRecord'    => false,
                'description' => 'A citation of a repository record, with the source call numbers held there and any notes.',
            ],
        ];
    }

    /**
     * Builds the generator's reference map: each generation target's substructure URI keyed to its
     * short class name and fully-qualified import. A generated container passes this to the generator
     * so that a nested substructure whose own class already exists is referenced as a typed property
     * rather than deferred and dropped, closing the structure graph edge by edge.
     *
     * @param Schema           $schema     The schema resolving each target's tag.
     * @param DomainClassifier $classifier The classifier resolving each target's namespace.
     *
     * @return array<string, array{0: string, 1: string}> The URI → [short class, FQCN] map.
     */
    public static function referenceMap(Schema $schema, DomainClassifier $classifier): array
    {
        $map = [];

        foreach (self::targets() as $target) {
            $definition = $schema->byUri($target['uri']);

            if (!$definition instanceof StructureDefinition) {
                continue;
            }

            $namespace = $classifier->namespaceFor($definition->tag, $target['isRecord']);

            $map[$target['uri']] = [$target['class'], $namespace . '\\' . $target['class']];
        }

        return $map;
    }
}
