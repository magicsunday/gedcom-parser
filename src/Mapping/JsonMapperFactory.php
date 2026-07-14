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
        // than mapped field by field, so each is registered as a custom type. DATE and AGE are
        // plain string leaves; PLAC carries both a value and a FORM substructure, so its shaped
        // node is an array from which the value object takes the place name and the form hierarchy.
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                DateValue::class,
                static fn (mixed $value): DateValue => DateValue::fromGedcom(self::requireString($value, 'DATE')),
            ),
        );
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                AgeValue::class,
                static fn (mixed $value): AgeValue => AgeValue::fromGedcom(self::requireString($value, 'AGE')),
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
     * Returns the value as a string, failing loud when a string-payload value-object leaf is
     * mis-shaped (a non-string reaching its handler).
     *
     * @param mixed  $value The shaped payload
     * @param string $label The tag name for the error message
     *
     * @return string
     *
     * @throws MappingException When the value is not a string
     */
    private static function requireString(mixed $value, string $label): string
    {
        if (!is_string($value)) {
            throw new MappingException(sprintf('Expected a string %s payload, got %s.', $label, get_debug_type($value)));
        }

        return $value;
    }

    /**
     * Builds a PlaceValue from a shaped PLAC node. PLAC carries both a place-name value and a FORM
     * substructure, so its shaped node is an array; the FORM hierarchy is passed through so the
     * value object can map the jurisdiction labels.
     *
     * @param mixed $value The shaped PLAC payload (an array, or a plain string when form-less)
     *
     * @return PlaceValue
     *
     * @throws MappingException When the value is neither a string nor a shaped array
     */
    private static function placeFromShaped(mixed $value): PlaceValue
    {
        if (is_string($value)) {
            return PlaceValue::fromGedcom($value);
        }

        if (is_array($value)) {
            $place = $value['value'] ?? null;
            $form  = $value['form'] ?? null;

            return PlaceValue::fromGedcom(
                is_string($place) ? $place : '',
                is_string($form) ? $form : null,
            );
        }

        throw new MappingException(sprintf('Expected a string or shaped PLAC payload, got %s.', get_debug_type($value)));
    }
}
