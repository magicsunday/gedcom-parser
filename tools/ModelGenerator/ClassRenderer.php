<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

use function implode;
use function max;
use function str_pad;
use function strlen;

/**
 * Renders a {@see ClassSpec} to a house-style, CGL-canonical `final readonly` model class: the
 * standard file header, `declare(strict_types=1)`, the namespace, the ordered imports, a
 * generated-file docblock carrying the `@author`/`@license`/`@link` triple, and a promoted
 * constructor whose column-aligned `@param` block mirrors the property specs. The output is emitted
 * already canonical, so re-running the code-style fixer over it is a no-op.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class ClassRenderer
{
    /**
     * Renders the class specification to a complete PHP source file (with a trailing newline).
     *
     * @param ClassSpec $spec The class specification to render.
     *
     * @return string The rendered PHP source.
     */
    public function render(ClassSpec $spec): string
    {
        $lines = [
            '<?php',
            '',
            '/**',
            ' * This file is part of the package magicsunday/gedcom-parser.',
            ' *',
            ' * For the full copyright and license information, please read the',
            ' * LICENSE file that was distributed with this source code.',
            ' */',
            '',
            'declare(strict_types=1);',
            '',
            'namespace ' . $spec->namespace . ';',
        ];

        if ($spec->imports !== []) {
            $lines[] = '';

            foreach ($spec->imports as $import) {
                $lines[] = 'use ' . $import . ';';
            }
        }

        $lines[] = '';
        $lines[] = '/**';
        $lines[] = ' * ' . $spec->description;
        $lines[] = ' *';
        $lines[] = ' * This class is generated from the GEDCOM registry. Do not edit it by hand.';
        $lines[] = ' *';
        $lines[] = ' * @author  Rico Sonntag <mail@ricosonntag.de>';
        $lines[] = ' * @license https://opensource.org/licenses/MIT';
        $lines[] = ' * @link    https://github.com/magicsunday/gedcom-parser/';
        $lines[] = ' */';
        $lines[] = 'final readonly class ' . $spec->className;
        $lines[] = '{';

        if ($spec->properties === []) {
            $lines[] = '    public function __construct()';
            $lines[] = '    {';
            $lines[] = '    }';
            $lines[] = '}';

            return implode("\n", $lines) . "\n";
        }

        // Left-align the @param type and name columns, so the block matches the code-style fixer's
        // canonical alignment and re-running it over the generated file is a no-op.
        $typeWidth = 0;
        $nameWidth = 0;

        foreach ($spec->properties as $property) {
            $typeWidth = max($typeWidth, strlen($property->docType));
            $nameWidth = max($nameWidth, strlen('$' . $property->name));
        }

        $lines[] = '    /**';

        foreach ($spec->properties as $property) {
            $lines[] = '     * @param '
                . str_pad($property->docType, $typeWidth)
                . ' '
                . str_pad('$' . $property->name, $nameWidth)
                . ' '
                . $property->description;
        }

        $lines[] = '     */';
        $lines[] = '    public function __construct(';

        foreach ($spec->properties as $property) {
            $lines[] = '        public ' . $property->phpType . ' $' . $property->name . ' = ' . $property->default . ',';
        }

        $lines[] = '    ) {';
        $lines[] = '    }';
        $lines[] = '}';

        return implode("\n", $lines) . "\n";
    }
}
