<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Schema;

use FilesystemIterator;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

use function is_array;
use function is_dir;
use function is_string;
use function sort;
use function str_contains;

/**
 * Compiles the vendored machine-readable GEDCOM registry into a {@see Schema} for a given
 * version.
 *
 * The registry ships one YAML file per structure. Because the same structure files carry both
 * the 5.5.1 terms (their URIs contain `/v5.5.1/`) and the 7.0 terms (`/v7/`), a single loader
 * derives either version's schema from the same source by selecting the matching URI slice — no
 * hand-authored per-version overlay is needed. Substructures are referenced by URI in the
 * registry; the loader resolves each to its child tag so the compiled definitions can be walked
 * by tag while mapping a parsed tree.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class RegistrySchemaLoader
{
    /**
     * @param string $registryPath The absolute path to the vendored `gedcom7-registries` directory.
     */
    public function __construct(
        private string $registryPath,
    ) {
    }

    /**
     * Compiles the schema for the given GEDCOM version.
     *
     * @param GedcomVersion $version The version whose structure slice to compile.
     *
     * @return Schema
     */
    public function load(GedcomVersion $version): Schema
    {
        $infix = $version->uriInfix();

        // First pass: parse every structure file, keep those belonging to the requested version,
        // and index their tags by URI so substructure references can be resolved to a tag.
        $parsed   = [];
        $tagByUri = [];

        foreach ($this->structureFiles() as $path) {
            $structure = $this->parseStructure($path);

            if ($structure === null) {
                continue;
            }

            if (!str_contains($structure['uri'], $infix)) {
                continue;
            }

            $parsed[]                    = $structure;
            $tagByUri[$structure['uri']] = $structure['tag'];
        }

        // Second pass: build the definitions, resolving each substructure URI to its child tag.
        // A tag may map to more than one candidate (5.5.1 inline-value vs pointer variants), so
        // each tag groups a list rather than a single substructure. Top-level records are also
        // indexed by tag so a parsed level-0 record resolves to its definition.
        $structures   = [];
        $recordsByTag = [];

        foreach ($parsed as $structure) {
            $substructures = [];

            foreach ($structure['substructures'] as $uri => $token) {
                $childTag = $tagByUri[$uri] ?? null;

                if ($childTag === null) {
                    // The reference points outside the version's slice (another version or an
                    // extension); it cannot be resolved to a tag here and is skipped.
                    continue;
                }

                $substructures[$childTag][] = new Substructure($uri, Cardinality::fromToken($token));
            }

            $definition = new StructureDefinition(
                $structure['uri'],
                $structure['tag'],
                $structure['payload'],
                $structure['enumerationSet'],
                $substructures,
            );

            $structures[$structure['uri']] = $definition;

            // The first record wins for a given tag; the standard slice has one record per tag.
            if ($structure['isRecord'] && !isset($recordsByTag[$structure['tag']])) {
                $recordsByTag[$structure['tag']] = $definition;
            }
        }

        return new Schema($structures, $recordsByTag);
    }

    /**
     * Returns the path of every standard structure YAML file in the registry, sorted so the
     * compiled schema's structure order is deterministic across filesystems.
     *
     * The directory is iterated rather than globbed so a registry path containing a glob
     * metacharacter cannot silently mis-scan; a missing directory yields nothing.
     *
     * @return list<string>
     */
    private function structureFiles(): array
    {
        $directory = $this->registryPath . '/structure/standard';

        if (!is_dir($directory)) {
            return [];
        }

        $paths = [];

        /** @var SplFileInfo $file */
        foreach (new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS) as $file) {
            if ($file->isFile() && ($file->getExtension() === 'yaml')) {
                $paths[] = $file->getPathname();
            }
        }

        sort($paths);

        return $paths;
    }

    /**
     * Parses one registry structure file into a typed intermediate shape, or NULL when the file
     * is not a usable structure definition.
     *
     * @param string $path The path to the structure YAML file.
     *
     * @return array{uri: string, tag: string, payload: string|null, enumerationSet: string|null, substructures: array<string, string>, isRecord: bool}|null
     */
    private function parseStructure(string $path): ?array
    {
        $data = Yaml::parseFile($path);

        if (!is_array($data)) {
            return null;
        }

        $uri = $data['uri'] ?? null;
        $tag = $data['standard tag'] ?? null;

        if (!is_string($uri) || !is_string($tag)) {
            return null;
        }

        $payload        = $data['payload'] ?? null;
        $enumerationSet = $data['enumeration set'] ?? null;

        $substructures    = [];
        $rawSubstructures = $data['substructures'] ?? null;

        if (is_array($rawSubstructures)) {
            foreach ($rawSubstructures as $substructureUri => $cardinalityToken) {
                if (is_string($substructureUri) && is_string($cardinalityToken)) {
                    $substructures[$substructureUri] = $cardinalityToken;
                }
            }
        }

        // The registry names every top-level data record `record-<TAG>` (record-INDI, …). This is
        // narrower and safer than "has no superstructures", which also matches the HEAD/TRLR
        // transmission structures and the CONT/CONC serialization pseudo-structures.
        $isRecord = str_contains($uri, '/record-');

        return [
            'uri'            => $uri,
            'tag'            => $tag,
            'payload'        => is_string($payload) ? $payload : null,
            'enumerationSet' => is_string($enumerationSet) ? $enumerationSet : null,
            'substructures'  => $substructures,
            'isRecord'       => $isRecord,
        ];
    }
}
