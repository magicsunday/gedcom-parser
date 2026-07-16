<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

use MagicSunday\Gedcom\Schema\Cardinality;

use function preg_match;
use function str_starts_with;
use function strtolower;

/**
 * Maps a leaf substructure — one carrying a payload rather than further substructures — onto a
 * typed {@see PropertySpec}. The payload decides the inner type (a pointer becomes the raw
 * cross-reference string, a known grammar payload its value object, an enumeration a tolerant
 * string, anything else a plain string), and the cardinality decides between a nullable single
 * value and a `list<>`.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class TypeMapper
{
    /**
     * The grammar value objects, keyed by the payload's `type-<name>` base name (the `#fragment`, as
     * in `type-Date#exact`, is ignored). Each maps to the short class name and its fully-qualified
     * import. A payload not listed here (and not a pointer or enumeration) maps to a plain string.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const array VALUE_OBJECTS = [
        'Date'         => ['DateValue', 'MagicSunday\\Gedcom\\ValueObject\\DateValue'],
        'DATE_VALUE'   => ['DateValue', 'MagicSunday\\Gedcom\\ValueObject\\DateValue'],
        'DATE_PERIOD'  => ['DateValue', 'MagicSunday\\Gedcom\\ValueObject\\DateValue'],
        'DATE_EXACT'   => ['DateValue', 'MagicSunday\\Gedcom\\ValueObject\\DateValue'],
        'PLACE_NAME'   => ['PlaceValue', 'MagicSunday\\Gedcom\\ValueObject\\PlaceValue'],
        'Age'          => ['AgeValue', 'MagicSunday\\Gedcom\\ValueObject\\AgeValue'],
        'AGE_AT_EVENT' => ['AgeValue', 'MagicSunday\\Gedcom\\ValueObject\\AgeValue'],
    ];

    /**
     * Maps a leaf substructure to its typed constructor property.
     *
     * @param string      $tag         The substructure's GEDCOM tag.
     * @param string|null $payload     The registry payload URI, or NULL for a value-less leaf.
     * @param Cardinality $cardinality The substructure's cardinality.
     *
     * @return PropertySpec The mapped property.
     */
    public function forLeaf(string $tag, ?string $payload, Cardinality $cardinality): PropertySpec
    {
        $name = strtolower($tag);

        [$inner, $import] = $this->innerType($payload);

        if ($cardinality->isCollection()) {
            return new PropertySpec($name, 'array', 'list<' . $inner . '>', '[]', 'The ' . $tag . ' values.', $import);
        }

        return new PropertySpec($name, '?' . $inner, $inner . '|null', 'null', 'The ' . $tag . ' value.', $import);
    }

    /**
     * Reports whether a payload maps to a grammar value object (whose custom mapping-layer handler
     * accepts the shaped array the object mapper produces for a substructure-bearing child). A
     * payload-bearing child that does NOT map to a value object cannot be a scalar leaf when it also
     * carries substructures, since the mapper would then supply an array — such a child needs its
     * own generated class instead.
     *
     * @param string|null $payload The registry payload URI, or NULL.
     *
     * @return bool Whether the payload maps to a value object.
     */
    public function mapsToValueObject(?string $payload): bool
    {
        return $this->innerType($payload)[1] !== null;
    }

    /**
     * Resolves the inner (non-nullable, non-collection) type name and its import from a payload URI.
     *
     * @param string|null $payload The registry payload URI, or NULL.
     *
     * @return array{0: string, 1: string|null} The inner type and its fully-qualified import (NULL for a primitive).
     */
    private function innerType(?string $payload): array
    {
        if (($payload === null) || ($payload === '')) {
            return ['string', null];
        }

        // A pointer payload (`@<…record-X>@`) is kept as the raw cross-reference string.
        if (str_starts_with($payload, '@<')) {
            return ['string', null];
        }

        $matches = [];

        // Capture the `type-<name>` base, tolerating a trailing `#fragment` such as `type-Date#exact`.
        // The delimiter is `~`, not `#`, so the literal `#` of the fragment does not close the pattern.
        if (preg_match('~/type-([A-Za-z0-9_-]+)(?:#[A-Za-z0-9_-]+)?$~', $payload, $matches) !== 1) {
            return ['string', null];
        }

        $type = $matches[1];

        // Enumerations stay tolerant strings; the typed constant holders are comparison targets.
        if (($type === 'Enum') || ($type === 'List-Enum')) {
            return ['string', null];
        }

        return self::VALUE_OBJECTS[$type] ?? ['string', null];
    }
}
