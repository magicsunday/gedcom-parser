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
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

use function array_key_exists;
use function class_exists;
use function count;
use function in_array;
use function preg_match_all;
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
        $shaped = $this->shape($node, $definition, $className);

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
     * Returns the tags the target class consumes as typed constructor properties, keyed by the
     * lowercased tag, so the shaper can divert a schema-recognised child the class does not model.
     *
     * @param class-string $className The target class.
     *
     * @return array<string, true> The consumed tags, keyed by the lowercased property/tag name.
     */
    private function consumedTags(string $className): array
    {
        $constructor = (new ReflectionClass($className))->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $tags = [];

        foreach ($constructor->getParameters() as $parameter) {
            // The divert compares against the shaped key, which is strtolower($child->tag). This
            // relies on the model invariant that every consumed property is named after its
            // lowercased tag (a single lowercase token that the mapper's camel-case converter maps
            // to itself), which both the hand-written records and the generator uphold. Were a
            // property ever named in camelCase, its tag would be wrongly diverted here — align this
            // with the mapper's name converter before introducing such a name.
            $tags[strtolower($parameter->getName())] = true;
        }

        return $tags;
    }

    /**
     * Resolves the typed model class a schema-recognised container child maps to on the given class,
     * so the nested shape can be made class-aware in turn. Returns NULL when the property maps to a
     * value-object leaf (hydrated by a type handler, whose substructures must not be diverted), to a
     * scalar, or is not modelled at all — in which case the nested shape does not divert. Memoized,
     * since it is asked once per container child of every mapped record.
     *
     * @param class-string|null $className The class being shaped, or NULL when it is unknown.
     * @param string            $property  The lowercased child tag / property name.
     *
     * @return class-string|null The nested model class, or NULL when the child is not a typed model.
     */
    private function nestedModelClass(?string $className, string $property): ?string
    {
        if ($className === null) {
            return null;
        }

        /** @var array<string, class-string|null> $cache */
        static $cache = [];

        $key = $className . '::' . $property;

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $class = $this->parameterClass($className, $property);

        // A value-object leaf is hydrated from its raw payload by a type handler, so its
        // substructures are that handler's input, not unmodelled tags — never shape it class-aware.
        if ($this->isLeafValueType($class)) {
            $class = null;
        }

        return $cache[$key] = $class;
    }

    /**
     * Determines whether a structureless container child must be shaped into an object rather than
     * kept as its bare value. This holds when the target property is a constructor-hydrated model
     * (such as an {@see \MagicSunday\Gedcom\Model\Substructure\Common\AliasLink}), which cannot accept
     * a bare scalar — its pointer/value must become the object's `xref`/`value`. A handler-backed
     * model (a {@see \MagicSunday\Gedcom\Model\Note} whose registered handler already resolves the
     * bare payload) is excluded, so its bare string reaches that handler unchanged.
     *
     * @param class-string|null $className The class being shaped, or NULL when it is unknown.
     * @param string            $property  The lowercased child tag / property name.
     *
     * @return bool True when the child must be shaped as an object.
     */
    private function requiresObjectShape(?string $className, string $property): bool
    {
        $class = $this->nestedModelClass($className, $property);

        return ($class !== null) && !in_array($class, JsonMapperFactory::HANDLER_BACKED_TYPES, true);
    }

    /**
     * Resolves the class a constructor parameter holds — its single named type, or the element class
     * of a `list<>`/`[]` collection read from the constructor's PHPDoc.
     *
     * @param class-string $className The class to inspect.
     * @param string       $property  The lowercased parameter name.
     *
     * @return class-string|null The parameter's class, or NULL when it is a scalar, an array of
     *                           scalars, or absent.
     */
    private function parameterClass(string $className, string $property): ?string
    {
        // Reflection + PHPDoc resolution is asked once per container child of every mapped record, so
        // memoize the (deterministic) result per class + property.
        /** @var array<string, class-string|null> $cache */
        static $cache = [];

        $key = $className . '::' . $property;

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $constructor = (new ReflectionClass($className))->getConstructor();

        if ($constructor === null) {
            return $cache[$key] = null;
        }

        $parameter = null;

        foreach ($constructor->getParameters() as $candidate) {
            if (strtolower($candidate->getName()) === $property) {
                $parameter = $candidate;

                break;
            }
        }

        if ($parameter === null) {
            return $cache[$key] = null;
        }

        $type = $parameter->getType();

        if (($type instanceof ReflectionNamedType) && !$type->isBuiltin() && class_exists($type->getName())) {
            return $cache[$key] = $type->getName();
        }

        return $cache[$key] = $this->collectionElementClass($constructor, $parameter->getName());
    }

    /**
     * Determines whether the given container child maps to a value-object leaf type (a handler-parsed
     * leaf such as DATE/PLAC/AGE). Such a leaf carries its own `$unknown`, so it is worth shaping as
     * an object to preserve an out-of-schema child even when it declares no schema substructures.
     *
     * @param class-string|null $className The class being shaped, or NULL when it is unknown.
     * @param string            $property  The lowercased child tag / property name.
     *
     * @return bool True when the child maps to a value-object leaf type.
     */
    private function isLeafValueChild(?string $className, string $property): bool
    {
        if ($className === null) {
            return false;
        }

        return $this->isLeafValueType($this->parameterClass($className, $property));
    }

    /**
     * Determines whether the given class is a value-object leaf type — one the mapper hydrates from a
     * raw payload through a registered type handler rather than from its shaped substructures.
     *
     * @param class-string|null $class The class to test, or NULL.
     *
     * @return bool True when the class is a value-object leaf type.
     */
    private function isLeafValueType(?string $class): bool
    {
        return ($class !== null) && in_array($class, JsonMapperFactory::LEAF_VALUE_TYPES, true);
    }

    /**
     * Reads the element class of a collection-typed constructor parameter from the constructor's
     * PHPDoc (`@param list<Foo> $bar` / `@param Foo[] $bar`), returning the first class it names.
     *
     * @param ReflectionMethod $constructor The constructor whose PHPDoc to read.
     * @param string           $name        The parameter name.
     *
     * @return class-string|null The element class, or NULL when the parameter names no class.
     */
    private function collectionElementClass(ReflectionMethod $constructor, string $name): ?string
    {
        if ($constructor->getDocComment() === false) {
            return null;
        }

        $context  = (new ContextFactory())->createFromReflector($constructor->getDeclaringClass());
        $docBlock = DocBlockFactory::createInstance()->create($constructor, $context);

        foreach ($docBlock->getTagsByName('param') as $tag) {
            if (!$tag instanceof Param) {
                continue;
            }

            if ($tag->getVariableName() !== $name) {
                continue;
            }

            return $this->firstClassIn((string) $tag->getType());
        }

        return null;
    }

    /**
     * Returns the first existing class named in a PHPDoc type expression (the type resolver writes
     * every class fully qualified with a leading backslash), or NULL when it names none.
     *
     * @param string $type The PHPDoc type expression (e.g. `list<\App\Foo>`).
     *
     * @return class-string|null The first existing class, or NULL.
     */
    private function firstClassIn(string $type): ?string
    {
        if (preg_match_all('/\\\\([A-Za-z0-9_\\\\]+)/', $type, $matches) === false) {
            return null;
        }

        foreach ($matches[1] as $candidate) {
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Shapes a node and its substructures into a property-name-keyed array.
     *
     * @param GedcomNode          $node       The node to shape.
     * @param StructureDefinition $definition The schema definition of the node's structure.
     * @param class-string|null   $className  The class this node is shaped into, so a schema-recognised
     *                                        child the class does not model is preserved verbatim
     *                                        rather than dropped. NULL when the class is unknown (a
     *                                        substructure mapped by a value handler, not a typed
     *                                        class), which does not divert.
     *
     * @return array<string, mixed>
     */
    private function shape(GedcomNode $node, StructureDefinition $definition, ?string $className = null): array
    {
        $consumedTags = ($className !== null) ? $this->consumedTags($className) : null;

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

        /** @var list<array<string, mixed>> $unknown */
        $unknown = [];

        foreach ($node->children as $child) {
            // A substructure must sit exactly one level below its parent; a deeper child is a
            // malformed level skip and is dropped rather than mis-attributed.
            if ($child->level !== ($node->level + 1)) {
                continue;
            }

            $substructure = $this->resolveSubstructure($definition, $child);

            if (!$substructure instanceof Substructure) {
                // A child whose tag is not a permitted substructure here — an extension
                // (`_`-prefixed vendor tag) or any tag out of the schema at this position — is
                // preserved verbatim on the target's `$unknown` list rather than dropped, so no
                // parsed data is silently lost. Its whole subtree is kept.
                $unknown[] = $this->rawShape($child);

                continue;
            }

            $childDefinition = $this->schema->byUri($substructure->uri);
            $property        = strtolower($child->tag);

            // A tag the schema permits here but the shaped class does not model as a property would
            // be shaped into a key the mapper silently drops. Preserve it verbatim on `$unknown`
            // instead — exactly like an out-of-schema tag — so no recognised data is lost. This runs
            // at every level whose class is known (the record and every nested typed model); a shape
            // whose class is unknown (a value-handler leaf) carries no consumed tags and does not
            // divert.
            if (($consumedTags !== null) && !array_key_exists($property, $consumedTags)) {
                $unknown[] = $this->rawShape($child);

                continue;
            }

            // Recurse when the child's definition declares substructures, so a structured tag
            // always yields the same object shape regardless of which substructures this instance
            // happens to carry; its own line value is preserved under the `value` key. The nested
            // shape is made class-aware for a child that maps to a typed model class, so its own
            // unmodelled substructures are diverted too; a value-object leaf (resolved to NULL)
            // stays class-unaware, since its substructures are its type handler's input. A
            // structureless value-object leaf (a 5.5.1 DATE/AGE) is also shaped as an object WHEN it
            // actually carries children, so an out-of-schema tag beneath it reaches its handler's
            // `$unknown` rather than being dropped with the bare string; a scalar leaf (no value
            // class) keeps the plain string, having nowhere to preserve a child. A tag whose
            // definition declares no substructures but whose target property is a constructor-
            // hydrated model (a 5.5.1 bare-pointer ALIA) is also shaped, so its pointer/value becomes
            // the object's `xref`/`value` rather than a bare string the constructor cannot accept; a
            // handler-backed model (Note) is excluded, since its handler already resolves the bare
            // payload.
            $recurse = ($childDefinition instanceof StructureDefinition)
                && (($childDefinition->substructures !== [])
                    || (($child->children !== []) && $this->isLeafValueChild($className, $property))
                    || $this->requiresObjectShape($className, $property));

            $value = $recurse
                ? $this->shape($child, $childDefinition, $this->nestedModelClass($className, $property))
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

        if ($unknown !== []) {
            $shaped['unknown'] = $unknown;
        }

        return $shaped;
    }

    /**
     * Shapes an unconsumed node and its whole subtree into a raw property array carried under the
     * `unknown` key, from which the mapper's {@see RawSubstructure} handler rebuilds the preserved
     * substructure verbatim. Unlike {@see shape()} this applies no schema resolution and keeps every
     * descendant, since the subtree is by definition outside the typed model.
     *
     * @param GedcomNode $node The unconsumed node to preserve.
     *
     * @return array<string, mixed> The raw shape: `tag`, `value`, `xref` and nested `children`.
     */
    private function rawShape(GedcomNode $node): array
    {
        $children = [];

        foreach ($node->children as $child) {
            $children[] = $this->rawShape($child);
        }

        return [
            'tag'      => $node->tag,
            'value'    => $node->value,
            'xref'     => $node->xref,
            'children' => $children,
        ];
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
