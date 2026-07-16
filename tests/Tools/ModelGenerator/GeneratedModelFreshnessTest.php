<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Tools\ModelGenerator;

use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\StructureDefinition;
use MagicSunday\Gedcom\Tools\ModelGenerator\DomainClassifier;
use MagicSunday\Gedcom\Tools\ModelGenerator\GeneratedModels;
use MagicSunday\Gedcom\Tools\ModelGenerator\ModelGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_get_contents;
use function str_replace;

/**
 * The generated-model freshness gate: every committed generated class must equal what the generator
 * produces today from the registry, so a committed class can never silently drift from the registry
 * or the generator. This runs the same manifest ({@see GeneratedModels}) the driver writes from.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GeneratedModels::class)]
#[UsesClass(ModelGenerator::class)]
#[UsesClass(DomainClassifier::class)]
#[UsesClass(RegistrySchemaLoader::class)]
class GeneratedModelFreshnessTest extends TestCase
{
    /**
     * @return array<string, array{0: array{version: GedcomVersion, uri: string, class: string, isRecord: bool, description: string}}>
     */
    public static function targetProvider(): array
    {
        $rows = [];

        foreach (GeneratedModels::targets() as $target) {
            $rows[$target['class']] = [$target];
        }

        return $rows;
    }

    /**
     * The committed generated class matches a fresh generation from the registry.
     *
     * @param array{version: GedcomVersion, uri: string, class: string, isRecord: bool, description: string} $target
     */
    #[DataProvider('targetProvider')]
    #[Test]
    public function theCommittedClassMatchesAFreshGeneration(array $target): void
    {
        $root       = dirname(__DIR__, 3);
        $schema     = (new RegistrySchemaLoader($root . '/docs/spec/gedcom7-registries'))->load($target['version']);
        $definition = $schema->byUri($target['uri']);

        self::assertInstanceOf(StructureDefinition::class, $definition);

        $classifier = new DomainClassifier();

        // Regenerate from the SAME reference map the driver used, so a class that nests another
        // generated container reproduces its typed reference rather than deferring it.
        $generated = (new ModelGenerator(GeneratedModels::referenceMap($schema, $classifier)))->generate(
            $definition,
            $schema,
            $target['isRecord'],
            $target['class'],
            $target['description'],
        );

        $namespace = $classifier->namespaceFor($definition->tag, $target['isRecord']);
        $relative  = str_replace(['MagicSunday\\Gedcom\\', '\\'], ['', '/'], $namespace);
        $path      = $root . '/src/' . $relative . '/' . $target['class'] . '.php';

        self::assertSame(
            file_get_contents($path),
            $generated,
            'The committed generated class has drifted from the registry — re-generate it.',
        );
    }
}
