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
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\JsonMapper;
use MagicSunday\JsonMapper\Converter\CamelCasePropertyNameConverter;
use MagicSunday\JsonMapper\Value\ClosureTypeHandler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

use function get_debug_type;
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

        // A GEDCOM value-object leaf is parsed from its raw payload string through its own grammar
        // rather than mapped field by field, so it is registered as a custom type. Further leaves
        // (PlaceValue, AgeValue) are registered once a typed-model field consumes them, together
        // with the handling of a leaf that also carries substructures.
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                DateValue::class,
                static function (mixed $value): DateValue {
                    // A value-object leaf is parsed from its payload string. A non-string here means
                    // the node was mis-shaped (a structure with substructures reaching a leaf
                    // handler); fail loud rather than silently parsing an empty date.
                    if (!is_string($value)) {
                        throw new MappingException(
                            sprintf('Expected a string DATE payload, got %s.', get_debug_type($value)),
                        );
                    }

                    return DateValue::fromGedcom($value);
                },
            ),
        );

        return $mapper;
    }
}
