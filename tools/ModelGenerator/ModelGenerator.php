<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\Schema\StructureDefinition;

use function array_keys;
use function ksort;
use function str_starts_with;
use function strtolower;

/**
 * Turns a registry {@see StructureDefinition} into a rendered typed model class, driving the
 * {@see TypeMapper} for its leaf substructures and the {@see ClassRenderer} for the output.
 *
 * A pointer structure keeps its target's cross-reference; a leaf substructure becomes a typed
 * property; a substructure already covered by a hand-written model (such as `NOTE` → {@see \MagicSunday\Gedcom\Model\Note})
 * reuses that class; and every class carries the `$unknown` catch-all so nothing the typed model
 * does not consume is lost. A substructure that is itself a container needs its own generated
 * class and is skipped until the full roll-out wires the whole structure graph.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class ModelGenerator
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
        'NOTE' => ['Note', 'MagicSunday\\Gedcom\\Model\\Note'],
        'SOUR' => ['SourceCitation', 'MagicSunday\\Gedcom\\Model\\Substructure\\Source\\SourceCitation'],
    ];

    /**
     * The fully-qualified name of the preserved-substructure value object every class carries.
     */
    private const string RAW_SUBSTRUCTURE = 'MagicSunday\\Gedcom\\ValueObject\\RawSubstructure';

    /**
     * The type mapper resolving a leaf substructure's typed property.
     */
    private readonly TypeMapper $typeMapper;

    /**
     * The renderer emitting the class source.
     */
    private readonly ClassRenderer $renderer;

    /**
     * Wires the type mapper and the class renderer.
     */
    public function __construct()
    {
        $this->typeMapper = new TypeMapper();
        $this->renderer   = new ClassRenderer();
    }

    /**
     * Generates the typed model class for the given structure.
     *
     * @param StructureDefinition $definition  The structure to generate a class for.
     * @param Schema              $schema      The schema resolving the structure's substructures.
     * @param string              $namespace   The target namespace.
     * @param string              $className   The target class name.
     * @param string              $description The one-line class description.
     *
     * @return string The rendered PHP source.
     */
    public function generate(
        StructureDefinition $definition,
        Schema $schema,
        string $namespace,
        string $className,
        string $description,
    ): string {
        /** @var list<PropertySpec> $properties */
        $properties = [];

        // A pointer structure keeps its referenced record's cross-reference.
        if (($definition->payload !== null) && str_starts_with($definition->payload, '@<')) {
            $properties[] = new PropertySpec(
                'xref',
                '?string',
                'string|null',
                'null',
                'The referenced record cross-reference, or NULL when the structure is not a pointer.',
            );
        }

        foreach ($definition->substructures as $tag => $substructures) {
            // A tag may declare more than one variant (an inline and a pointer form, e.g. SOUR/OBJE).
            // A known model covers both variants at once; for any other same-tag pair only the first
            // variant is mapped here, and unifying the rest is deferred to the roll-out.
            $substructure = $substructures[0] ?? null;

            if ($substructure === null) {
                continue;
            }

            if (isset(self::KNOWN_MODELS[$tag])) {
                [$short, $fqcn] = self::KNOWN_MODELS[$tag];
                $name           = strtolower($tag);

                $properties[] = $substructure->cardinality->isCollection()
                    ? new PropertySpec($name, 'array', 'list<' . $short . '>', '[]', 'The ' . $tag . ' substructures.', $fqcn)
                    : new PropertySpec($name, '?' . $short, $short . '|null', 'null', 'The ' . $tag . ' substructure.', $fqcn);

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
                && !$this->typeMapper->mapsToValueObject($childDefinition->payload)
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
}
