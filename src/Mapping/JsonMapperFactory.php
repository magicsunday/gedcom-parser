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
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\JsonMapper;
use MagicSunday\JsonMapper\Converter\CamelCasePropertyNameConverter;
use MagicSunday\JsonMapper\Value\ClosureTypeHandler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

use function array_key_exists;
use function get_debug_type;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Builds a {@see JsonMapper} configured for the typed GEDCOM model.
 *
 * The mapper is wired with reflection- and PHPDoc-based type extraction so it can read the typed
 * model's constructor parameters and collection annotations, and constructs the immutable
 * `final readonly` records through their constructors (the constructor-hydration support added in
 * jsonmapper 3.1).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class JsonMapperFactory
{
    /**
     * Private constructor; use {@see create()}.
     */
    private function __construct()
    {
    }

    /**
     * Creates a mapper configured for the typed GEDCOM model.
     *
     * @return JsonMapper
     */
    public static function create(): JsonMapper
    {
        $mapper = new JsonMapper(
            new PropertyInfoExtractor([new ReflectionExtractor()], [new PhpDocExtractor()]),
            PropertyAccess::createPropertyAccessor(),
            new CamelCasePropertyNameConverter(),
        );

        // A GEDCOM value-object leaf is parsed from its raw payload through its own grammar rather
        // than mapped field by field, so each is registered as a custom type. A leaf that also
        // declares substructures is shaped as an array carrying its own line value under the
        // `value` key (a GEDCOM 7.0 DATE/AGE carries PHRASE/TIME; PLAC carries FORM), so each
        // handler resolves the leaf value from either a bare string or that shaped array.
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                DateValue::class,
                static fn (mixed $value): DateValue => DateValue::fromGedcom(self::leafValue($value, 'DATE')),
            ),
        );
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                AgeValue::class,
                static fn (mixed $value): AgeValue => AgeValue::fromGedcom(self::leafValue($value, 'AGE')),
            ),
        );
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                PlaceValue::class,
                static fn (mixed $value): PlaceValue => self::placeFromShaped($value),
            ),
        );

        return $mapper;
    }

    /**
     * Resolves the string leaf payload of a value-object node. A leaf that also declares
     * substructures is shaped as an array carrying its own line value under the `value` key (a
     * GEDCOM 7.0 DATE/AGE carries PHRASE/TIME), so the value is taken from that key; a value-less
     * leaf resolves to the empty string. A non-string, non-array payload is a mis-shape and fails
     * loud.
     *
     * @param mixed  $value The shaped payload (a bare string, or a shaped array)
     * @param string $label The tag name for the error message
     *
     * @return string
     *
     * @throws MappingException When the value is neither a string nor a shaped array
     */
    private static function leafValue(mixed $value, string $label): string
    {
        // A value-less substructure (e.g. an empty FORM line) is shaped as a null leaf; it resolves
        // as absent (the empty string), the same as a shaped leaf with no `value` key.
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            // A value-less leaf (e.g. a 7.0 DATE carrying only a PHRASE) has no `value` key and
            // resolves to the empty string; a `value` key that is present but not a string is a
            // genuine mis-shape and fails loud rather than being silently coerced away.
            if (!array_key_exists('value', $value)) {
                return '';
            }

            $inner = $value['value'];

            if (!is_string($inner)) {
                throw new MappingException(sprintf('Expected a string %s value, got %s.', $label, get_debug_type($inner)));
            }

            return $inner;
        }

        throw new MappingException(sprintf('Expected a string or shaped %s payload, got %s.', $label, get_debug_type($value)));
    }

    /**
     * Builds a PlaceValue from a shaped PLAC node. PLAC carries both a place-name value and a FORM
     * substructure, so its shaped node is an array; the place name is resolved as a leaf value and
     * the FORM hierarchy passed through so the value object can map the jurisdiction labels.
     *
     * @param mixed $value The shaped PLAC payload (an array, or a plain string when form-less)
     *
     * @return PlaceValue
     *
     * @throws MappingException When the value is neither a string nor a shaped array
     */
    private static function placeFromShaped(mixed $value): PlaceValue
    {
        $name = self::leafValue($value, 'PLAC');
        $form = null;

        if (is_array($value) && array_key_exists('form', $value)) {
            // Resolve the FORM through the same leaf helper as the place name, so a shaped FORM is
            // handled and a mis-shaped one fails loud consistently rather than being coerced away.
            $form = self::leafValue($value['form'], 'FORM');
        }

        return PlaceValue::fromGedcom($name, $form);
    }
}
