<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\Schema\Cardinality;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\Schema\StructureDefinition;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

use function array_keys;
use function ksort;
use function str_starts_with;
use function strtolower;

/**
 * Turns a registry {@see StructureDefinition} into a rendered typed model class, driving the
 * {@see TypeMapper} for its leaf substructures and the {@see ClassRenderer} for the output.
 *
 * A pointer structure keeps its target's cross-reference; a leaf substructure becomes a typed
 * property; a substructure already covered by a hand-written model (such as `NOTE` → {@see Note})
 * reuses that class; a substructure whose own generated container class already exists (the
 * reference map) is referenced as a typed property; and every class carries the `$unknown` catch-all
 * so nothing the typed model does not consume is lost. A container substructure not yet in the
 * reference map still needs its own generated class and is deferred until the roll-out adds it.
 *
 * This is the generator ENGINE; its type-mapping table is refined, structure by structure, by the
 * full roll-out. One table gap remains deferred there: a same-tag inline/pointer pair on a
 * non-known tag maps only its first variant. No committed generated class exercises that gap yet, so
 * it does not affect the shipped model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class ModelGenerator
{
    /**
     * Substructures already covered by a hand-written model, keyed by tag: the short class name and
     * its fully-qualified import. A tag listed here maps to that model regardless of its inline /
     * pointer variants, so a same-tag pointer+inline pair (such as `SOUR` or `NOTE`) collapses onto
     * the one model rather than the first variant only.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const array KNOWN_MODELS = [
        'NOTE' => ['Note', Note::class],
        'SOUR' => ['SourceCitation', SourceCitation::class],
    ];

    /**
     * The fully-qualified name of the preserved-substructure value object every class carries.
     */
    private const string RAW_SUBSTRUCTURE = RawSubstructure::class;

    /**
     * The type mapper resolving a leaf substructure's typed property.
     */
    private TypeMapper $typeMapper;

    /**
     * The renderer emitting the class source.
     */
    private ClassRenderer $renderer;

    /**
     * The classifier resolving the target namespace.
     */
    private DomainClassifier $classifier;

    /**
     * Wires the type mapper, the class renderer and the domain classifier.
     *
     * @param array<string, array{0: string, 1: string}> $generatedByUri The reference map of
     *                                                                   substructure URIs whose own
     *                                                                   generated class already
     *                                                                   exists, each keyed to its
     *                                                                   short class name and its
     *                                                                   fully-qualified import; a
     *                                                                   substructure listed here is
     *                                                                   referenced as a typed
     *                                                                   property rather than
     *                                                                   deferred.
     */
    public function __construct(private array $generatedByUri = [])
    {
        $this->typeMapper = new TypeMapper();
        $this->renderer   = new ClassRenderer();
        $this->classifier = new DomainClassifier();
    }

    /**
     * Generates the typed model class for the given structure, placing it in the namespace the
     * domain classifier resolves for its tag.
     *
     * @param StructureDefinition $definition  The structure to generate a class for.
     * @param Schema              $schema      The schema resolving the structure's substructures.
     * @param bool                $isRecord    Whether the structure is a level-0 record.
     * @param string              $className   The target class name.
     * @param string              $description The one-line class description.
     *
     * @return string The rendered PHP source.
     */
    public function generate(
        StructureDefinition $definition,
        Schema $schema,
        bool $isRecord,
        string $className,
        string $description,
    ): string {
        $namespace = $this->classifier->namespaceFor($definition->tag, $isRecord);

        /** @var list<PropertySpec> $properties */
        $properties = [];

        // A pointer structure keeps its referenced record's cross-reference; a structure carrying a
        // non-pointer payload keeps that payload as its own typed line value.
        if ($definition->payload !== null) {
            $properties[] = str_starts_with($definition->payload, '@<')
                ? new PropertySpec(
                    'xref',
                    '?string',
                    'string|null',
                    'null',
                    'The referenced record cross-reference, or NULL when the structure is not a pointer.',
                )
                : $this->typeMapper->forValue($definition->payload);
        }

        foreach ($definition->substructures as $tag => $substructures) {
            // A tag may declare more than one variant (an inline and a pointer form, e.g. SOUR/OBJE).
            // A known model covers both variants at once; for any other same-tag pair only the first
            // variant is mapped here, and unifying the rest is deferred to the roll-out.
            $substructure = $substructures[0] ?? null;

            if ($substructure === null) {
                continue;
            }

            // A hand-written model covers this tag (by tag, both its inline and pointer variant).
            if (isset(self::KNOWN_MODELS[$tag])) {
                [$short, $fqcn] = self::KNOWN_MODELS[$tag];

                $properties[] = $this->classReference($tag, $short, $fqcn, $substructure->cardinality);

                continue;
            }

            // The substructure's own generated container class already exists (reference map): use
            // it rather than deferring the container and dropping its data.
            if (isset($this->generatedByUri[$substructure->uri])) {
                [$short, $fqcn] = $this->generatedByUri[$substructure->uri];

                $properties[] = $this->classReference($tag, $short, $fqcn, $substructure->cardinality);

                continue;
            }

            $childDefinition = $schema->byUri($substructure->uri);

            if (!$childDefinition instanceof StructureDefinition) {
                continue;
            }

            // A substructure-bearing child is deferred to its own generated class UNLESS its payload
            // maps to a grammar value object, whose handler accepts the shaped array the mapper
            // produces (a DATE carries TIME/PHRASE yet maps to a DateValue). A value-less container
            // (DATA) and a non-value-object payload with substructures (ADDR carries ADR1/CITY and a
            // string, which the mapper shapes as an array, not a scalar) both need their own class.
            if (($childDefinition->substructures !== [])
                && !$this->typeMapper->mapsToValueObject($childDefinition->tag, $childDefinition->payload)
            ) {
                continue;
            }

            $properties[] = $this->typeMapper->forLeaf(
                $childDefinition->tag,
                $childDefinition->payload,
                $substructure->cardinality,
            );
        }

        // Every generated class preserves the substructures the typed model does not consume.
        $properties[] = new PropertySpec(
            'unknown',
            'array',
            'list<RawSubstructure>',
            '[]',
            'Substructures the typed model did not consume (extension and out-of-place tags), preserved verbatim.',
            self::RAW_SUBSTRUCTURE,
        );

        /** @var array<string, true> $imports */
        $imports = [];

        foreach ($properties as $property) {
            if ($property->import !== null) {
                $imports[$property->import] = true;
            }
        }

        ksort($imports);

        return $this->renderer->render(
            new ClassSpec($namespace, $className, array_keys($imports), $description, $properties),
        );
    }

    /**
     * Builds the typed property referencing another model class (a hand-written model or an already
     * generated container), keyed by the tag and pluralised to a `list<>` for a collection.
     *
     * @param string      $tag         The substructure's tag, giving the property name.
     * @param string      $short       The referenced class's short name.
     * @param string      $fqcn        The referenced class's fully-qualified import.
     * @param Cardinality $cardinality The substructure's cardinality.
     *
     * @return PropertySpec The referencing property.
     */
    private function classReference(string $tag, string $short, string $fqcn, Cardinality $cardinality): PropertySpec
    {
        $name = strtolower($tag);

        return $cardinality->isCollection()
            ? new PropertySpec($name, 'array', 'list<' . $short . '>', '[]', 'The ' . $tag . ' substructures.', $fqcn)
            : new PropertySpec($name, '?' . $short, $short . '|null', 'null', 'The ' . $tag . ' substructure.', $fqcn);
    }
}
