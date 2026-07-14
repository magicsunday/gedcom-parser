<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Mapping;

use MagicSunday\JsonMapper;
use MagicSunday\JsonMapper\Converter\CamelCasePropertyNameConverter;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

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
        return new JsonMapper(
            new PropertyInfoExtractor([new ReflectionExtractor()], [new PhpDocExtractor()]),
            PropertyAccess::createPropertyAccessor(),
            new CamelCasePropertyNameConverter(),
        );
    }
}
