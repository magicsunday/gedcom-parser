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
     * The grammar value objects, keyed by the payload's `type-<name>` suffix. A payload not listed
     * here (and not a pointer or enumeration) maps to a plain string.
     *
     * @var array<string, string>
     */
    private const array VALUE_OBJECTS = [
        'Date'         => 'DateValue',
        'Date-exact'   => 'DateValue',
        'DATE_VALUE'   => 'DateValue',
        'DATE_PERIOD'  => 'DateValue',
        'DATE_EXACT'   => 'DateValue',
        'PLACE_NAME'   => 'PlaceValue',
        'Age'          => 'AgeValue',
        'AGE_AT_EVENT' => 'AgeValue',
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
        $name  = strtolower($tag);
        $inner = $this->innerType($payload);

        if ($cardinality->isCollection()) {
            return new PropertySpec($name, 'array', 'list<' . $inner . '>', '[]', 'The ' . $tag . ' values.');
        }

        return new PropertySpec($name, '?' . $inner, $inner . '|null', 'null', 'The ' . $tag . ' value.');
    }

    /**
     * Resolves the inner (non-nullable, non-collection) type name from a payload URI.
     *
     * @param string|null $payload The registry payload URI, or NULL.
     *
     * @return string The inner type: a value-object class name, or `string`.
     */
    private function innerType(?string $payload): string
    {
        if (($payload === null) || ($payload === '')) {
            return 'string';
        }

        // A pointer payload (`@<…record-X>@`) is kept as the raw cross-reference string.
        if (str_starts_with($payload, '@<')) {
            return 'string';
        }

        $matches = [];

        if (preg_match('#/type-([A-Za-z0-9_-]+)$#', $payload, $matches) !== 1) {
            return 'string';
        }

        $type = $matches[1];

        // Enumerations stay tolerant strings; the typed constant holders are comparison targets.
        if (($type === 'Enum') || ($type === 'List-Enum')) {
            return 'string';
        }

        return self::VALUE_OBJECTS[$type] ?? 'string';
    }
}
