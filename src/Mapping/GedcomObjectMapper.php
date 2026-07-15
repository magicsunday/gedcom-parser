<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Mapping;

use MagicSunday\Gedcom\Exception\MappingException;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\Schema\StructureDefinition;
use MagicSunday\Gedcom\Schema\Substructure;
use MagicSunday\JsonMapper;
use Throwable;

use function array_key_exists;
use function count;
use function sprintf;
use function str_starts_with;
use function strtolower;

/**
 * Maps a parsed {@see GedcomNode} tree onto a typed model object, driven by the registry schema.
 *
 * The mapper shapes a node subtree into a property-name-keyed array — the record identifier and
 * line value become fields, and each substructure is resolved through the schema to a property
 * (a single value or a list per its cardinality, a nested shape when its definition declares
 * substructures) — and hands that array to {@see JsonMapper}, whose constructor hydration builds
 * the immutable `final readonly` target.
 *
 * The property name is the lowercased tag (`NAME` becomes `name`); standard GEDCOM tags are
 * uppercase, so distinct tags never collide. A tag that the schema splits into an inline-value
 * and a cross-reference-pointer variant (`NOTE`, `SOUR`, `OBJE`, `REPO`) shares one property; the
 * variant is disambiguated by whether the parsed line carries a pointer. A child whose level is
 * not exactly the parent's level plus one is skipped, enforcing the substructure-nesting rule the
 * version-agnostic tree builder deliberately leaves to this layer.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class GedcomObjectMapper
{
    /**
     * @param Schema     $schema     The compiled schema resolving substructures to their definitions.
     * @param JsonMapper $jsonMapper The mapper building the typed target from the shaped array.
     */
    public function __construct(
        private Schema $schema,
        private JsonMapper $jsonMapper,
    ) {
    }

    /**
     * Maps the given node onto an instance of the target class.
     *
     * @template T of object
     *
     * @param GedcomNode          $node       The node to map.
     * @param StructureDefinition $definition The schema definition of the node's structure.
     * @param class-string<T>     $className  The target class to build.
     *
     * @return T The mapped, typed object.
     *
     * @throws MappingException When the mapper does not produce an instance of the target class.
     */
    public function map(GedcomNode $node, StructureDefinition $definition, string $className): object
    {
        $shaped = $this->shape($node, $definition);

        try {
            $mapped = $this->jsonMapper->map($shaped, $className);
        } catch (Throwable $exception) {
            // Keep every failure of the underlying mapper (its own exceptions, a TypeError from a
            // mismatched payload, a reflection failure) inside the library's own exception group.
            // The shaping above runs outside the try so a defect there is not masked as a mapping
            // failure.
            throw new MappingException(
                sprintf('Unable to map a "%s": %s', $className, $exception->getMessage()),
                0,
                $exception,
            );
        }

        if (!$mapped instanceof $className) {
            throw new MappingException(sprintf('Mapping did not produce an instance of "%s".', $className));
        }

        return $mapped;
    }

    /**
     * Maps a level-0 record node onto the given class, resolving the record definition from the
     * node's tag through the schema so the caller need not supply it.
     *
     * @template T of object
     *
     * @param GedcomNode      $node      The record node to map.
     * @param class-string<T> $className The target class to build.
     *
     * @return T The mapped, typed object.
     *
     * @throws MappingException When the node's tag is not a top-level record in the schema.
     */
    public function mapRecord(GedcomNode $node, string $className): object
    {
        if ($node->level !== 0) {
            throw new MappingException(sprintf('A record must be at level 0, got level %d.', $node->level));
        }

        $definition = $this->schema->recordByTag($node->tag);

        if (!$definition instanceof StructureDefinition) {
            throw new MappingException(sprintf('The tag "%s" is not a top-level record.', $node->tag));
        }

        return $this->map($node, $definition, $className);
    }

    /**
     * Shapes a node and its substructures into a property-name-keyed array.
     *
     * @param GedcomNode          $node       The node to shape.
     * @param StructureDefinition $definition The schema definition of the node's structure.
     *
     * @return array<string, mixed>
     */
    private function shape(GedcomNode $node, StructureDefinition $definition): array
    {
        $shaped = [];

        if ($node->identifier !== null) {
            $shaped['xref'] = $node->identifier;
        } elseif ($node->xref !== null) {
            $shaped['xref'] = $node->xref;
        }

        if ($node->value !== null) {
            $shaped['value'] = $node->value;
        }

        /** @var array<string, list<mixed>> $collections */
        $collections = [];

        foreach ($node->children as $child) {
            // A substructure must sit exactly one level below its parent; a deeper child is a
            // malformed level skip and is dropped rather than mis-attributed.
            if ($child->level !== ($node->level + 1)) {
                continue;
            }

            $substructure = $this->resolveSubstructure($definition, $child);

            if (!$substructure instanceof Substructure) {
                continue;
            }

            $childDefinition = $this->schema->byUri($substructure->uri);
            $property        = strtolower($child->tag);

            // Recurse when the child's definition declares substructures, so a structured tag
            // always yields the same object shape regardless of which substructures this instance
            // happens to carry; its own line value is preserved under the `value` key.
            $value = (($childDefinition instanceof StructureDefinition) && ($childDefinition->substructures !== []))
                ? $this->shape($child, $childDefinition)
                : ($child->value ?? $child->xref);

            if ($substructure->cardinality->isCollection()) {
                $collections[$property][] = $value;
            } elseif (!array_key_exists($property, $shaped)) {
                // A single-cardinality substructure keeps its first occurrence; a duplicate in
                // malformed input is ignored rather than silently overwriting.
                $shaped[$property] = $value;
            }
        }

        foreach ($collections as $property => $values) {
            $shaped[$property] = $values;
        }

        return $shaped;
    }

    /**
     * Resolves the substructure a child node maps to, disambiguating same-tag variants by whether
     * the child carries a cross-reference pointer.
     *
     * @param StructureDefinition $definition The parent structure definition.
     * @param GedcomNode          $child      The child node to resolve.
     *
     * @return Substructure|null The resolved substructure, or NULL when the tag is not permitted.
     */
    private function resolveSubstructure(StructureDefinition $definition, GedcomNode $child): ?Substructure
    {
        $candidates = $definition->substructuresFor($child->tag);

        if ($candidates === []) {
            return null;
        }

        if (count($candidates) === 1) {
            return $candidates[0];
        }

        $wantsPointer = $child->xref !== null;

        foreach ($candidates as $candidate) {
            $candidateDefinition = $this->schema->byUri($candidate->uri);
            $isPointer           = ($candidateDefinition instanceof StructureDefinition)
                && ($candidateDefinition->payload !== null)
                && str_starts_with($candidateDefinition->payload, '@<');

            if ($isPointer === $wantsPointer) {
                return $candidate;
            }
        }

        return $candidates[0];
    }
}
