<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

/**
 * The specification of a single generated model class: its namespace, name, imports, description
 * and constructor properties. Rendered to PHP by the {@see ClassRenderer}.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class ClassSpec
{
    /**
     * @param string             $namespace   The class namespace (without a leading backslash).
     * @param string             $className   The short class name.
     * @param list<string>       $imports     The fully-qualified class imports, in the order to emit.
     * @param string             $description The one-line class description for the docblock.
     * @param list<PropertySpec> $properties  The constructor properties, in declaration order.
     */
    public function __construct(
        public string $namespace,
        public string $className,
        public array $imports,
        public string $description,
        public array $properties,
    ) {
    }
}
