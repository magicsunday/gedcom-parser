<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Tools\ModelGenerator;

use MagicSunday\Gedcom\Tools\ModelGenerator\DomainClassifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the {@see DomainClassifier} places each generated class in the right namespace: a
 * level-0 record under `Model\Record`, and a substructure under `Model\Substructure\<Domain>` by an
 * explicit tag-to-domain table, defaulting to `Common`.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(DomainClassifier::class)]
class DomainClassifierTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: bool, 2: string}>
     */
    public static function tagProvider(): array
    {
        return [
            // tag, isRecord, expected namespace
            'record individual' => ['INDI', true, 'MagicSunday\\Gedcom\\Model\\Record'],
            'record source'     => ['SOUR', true, 'MagicSunday\\Gedcom\\Model\\Record'],
            'source citation'   => ['SOUR', false, 'MagicSunday\\Gedcom\\Model\\Substructure\\Source'],
            'place'             => ['PLAC', false, 'MagicSunday\\Gedcom\\Model\\Substructure\\Place'],
            'name'              => ['NAME', false, 'MagicSunday\\Gedcom\\Model\\Substructure\\Name'],
            'event'             => ['BIRT', false, 'MagicSunday\\Gedcom\\Model\\Substructure\\Event'],
            'attribute'         => ['OCCU', false, 'MagicSunday\\Gedcom\\Model\\Substructure\\Attribute'],
            'unknown → common'  => ['ADDR', false, 'MagicSunday\\Gedcom\\Model\\Substructure\\Common'],
        ];
    }

    /**
     * A tag resolves to the expected target namespace.
     */
    #[DataProvider('tagProvider')]
    #[Test]
    public function itResolvesTheTargetNamespace(string $tag, bool $isRecord, string $expected): void
    {
        self::assertSame($expected, (new DomainClassifier())->namespaceFor($tag, $isRecord));
    }
}
