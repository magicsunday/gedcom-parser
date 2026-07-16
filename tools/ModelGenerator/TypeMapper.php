<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

use MagicSunday\Gedcom\Model\ExactDate;
use MagicSunday\Gedcom\Schema\Cardinality;
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;

use function preg_match;
use function str_contains;
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
        'Date'         => ['DateValue', DateValue::class],
        'DATE_VALUE'   => ['DateValue', DateValue::class],
        'DATE_PERIOD'  => ['DateValue', DateValue::class],
        'PLACE_NAME'   => ['PlaceValue', PlaceValue::class],
        'Age'          => ['AgeValue', AgeValue::class],
        'AGE_AT_EVENT' => ['AgeValue', AgeValue::class],
    ];

    /**
     * Value objects resolved by their GEDCOM tag rather than payload, because the tag is stable
     * across versions while the payload URI is not (GEDCOM 7.0 `PLAC` carries `type-List#Text`, not
     * `type-PLACE_NAME`). Each maps to the short class name and its fully-qualified import.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const array VALUE_OBJECTS_BY_TAG = [
        'PLAC' => ['PlaceValue', PlaceValue::class],
        'AGE'  => ['AgeValue', AgeValue::class],
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

        [$inner, $import] = $this->innerType($tag, $payload);

        if ($cardinality->isCollection()) {
            return new PropertySpec($name, 'array', 'list<' . $inner . '>', '[]', 'The ' . $tag . ' values.', $import);
        }

        return new PropertySpec($name, '?' . $inner, $inner . '|null', 'null', 'The ' . $tag . ' value.', $import);
    }

    /**
     * Maps a structure's own (non-pointer) payload to its `value` property — the structure's line
     * value, typed from the payload.
     *
     * @param string|null $payload The registry payload URI.
     *
     * @return PropertySpec The `value` property.
     */
    public function forValue(?string $payload): PropertySpec
    {
        [$inner, $import] = $this->innerType('', $payload);

        return new PropertySpec('value', '?' . $inner, $inner . '|null', 'null', "The structure's line value.", $import);
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
    public function mapsToValueObject(string $tag, ?string $payload): bool
    {
        return $this->innerType($tag, $payload)[1] !== null;
    }

    /**
     * Resolves the inner (non-nullable, non-collection) type name and its import from a tag and
     * payload. Some value objects are resolved by tag (version-stable) and the exact-date structure
     * maps to the TIME-bearing ExactDate model; otherwise the payload URI decides.
     *
     * @param string      $tag     The substructure's GEDCOM tag.
     * @param string|null $payload The registry payload URI, or NULL.
     *
     * @return array{0: string, 1: string|null} The inner type and its fully-qualified import (NULL for a primitive).
     */
    private function innerType(string $tag, ?string $payload): array
    {
        // A value object whose tag is stable across versions even though its payload URI is not.
        if (isset(self::VALUE_OBJECTS_BY_TAG[$tag])) {
            return self::VALUE_OBJECTS_BY_TAG[$tag];
        }

        // An exact date (CHAN/CREA) carries a TIME, so it maps to the ExactDate model, not DateValue.
        if (($tag === 'DATE')
            && ($payload !== null)
            && (str_contains($payload, '#exact') || str_contains($payload, 'DATE_EXACT'))
        ) {
            return ['ExactDate', ExactDate::class];
        }

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
