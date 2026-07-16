<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Tools\ModelGenerator;

use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Tools\ModelGenerator\ClassRenderer;
use MagicSunday\Gedcom\Tools\ModelGenerator\ClassSpec;
use MagicSunday\Gedcom\Tools\ModelGenerator\PropertySpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the {@see ClassRenderer} emits a house-style-compliant, CGL-canonical `final readonly`
 * model class from a {@see ClassSpec}: the standard file header, `declare(strict_types=1)`, the
 * namespace, ordered imports, a generated-file docblock with the `@author`/`@license`/`@link`
 * triple, and a promoted constructor whose aligned `@param` block matches the property specs.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(ClassRenderer::class)]
#[UsesClass(ClassSpec::class)]
#[UsesClass(PropertySpec::class)]
class ClassRendererTest extends TestCase
{
    /**
     * A class spec with imports and two aligned properties renders to the exact expected PHP source.
     */
    #[Test]
    public function itRendersAClassSpecToCanonicalPhp(): void
    {
        $spec = new ClassSpec(
            'MagicSunday\\Gedcom\\Model\\Substructure\\Source',
            'Demo',
            [Note::class],
            'A demo generated citation.',
            [
                new PropertySpec('page', '?string', 'string|null', 'null', 'The page within the source.'),
                new PropertySpec('note', 'array', 'list<Note>', '[]', 'The inline notes.'),
            ],
        );

        $expected = <<<'PHP'
            <?php

            /**
             * This file is part of the package magicsunday/gedcom-parser.
             *
             * For the full copyright and license information, please read the
             * LICENSE file that was distributed with this source code.
             */

            declare(strict_types=1);

            namespace MagicSunday\Gedcom\Model\Substructure\Source;

            use MagicSunday\Gedcom\Model\Note;

            /**
             * A demo generated citation.
             *
             * This class is generated from the GEDCOM registry. Do not edit it by hand.
             *
             * @author  Rico Sonntag <mail@ricosonntag.de>
             * @license https://opensource.org/licenses/MIT
             * @link    https://github.com/magicsunday/gedcom-parser/
             */
            final readonly class Demo
            {
                /**
                 * @param string|null $page The page within the source.
                 * @param list<Note>  $note The inline notes.
                 */
                public function __construct(
                    public ?string $page = null,
                    public array $note = [],
                ) {
                }
            }

            PHP;

        self::assertSame($expected, (new ClassRenderer())->render($spec));
    }

    /**
     * A class spec with no imports and no properties renders a bare class with an empty constructor.
     */
    #[Test]
    public function itRendersAClassWithNoImportsOrProperties(): void
    {
        $spec = new ClassSpec('MagicSunday\\Gedcom\\Model', 'Blank', [], 'A blank class.', []);

        $expected = <<<'PHP'
            <?php

            /**
             * This file is part of the package magicsunday/gedcom-parser.
             *
             * For the full copyright and license information, please read the
             * LICENSE file that was distributed with this source code.
             */

            declare(strict_types=1);

            namespace MagicSunday\Gedcom\Model;

            /**
             * A blank class.
             *
             * This class is generated from the GEDCOM registry. Do not edit it by hand.
             *
             * @author  Rico Sonntag <mail@ricosonntag.de>
             * @license https://opensource.org/licenses/MIT
             * @link    https://github.com/magicsunday/gedcom-parser/
             */
            final readonly class Blank
            {
                public function __construct()
                {
                }
            }

            PHP;

        self::assertSame($expected, (new ClassRenderer())->render($spec));
    }
}
