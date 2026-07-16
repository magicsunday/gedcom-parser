<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Tools\ModelGenerator;

use MagicSunday\Gedcom\Model\Substructure\Common\CallNumber;
use MagicSunday\Gedcom\Model\Substructure\Source\RepositoryCitation;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Tools\ModelGenerator\DomainClassifier;
use MagicSunday\Gedcom\Tools\ModelGenerator\GeneratedModels;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * The manifest's reference map resolves each generation target's substructure URI to its short class
 * name and fully-qualified import, using the same domain classifier the generator does, so a
 * generated container can reference a nested generated container across domains.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GeneratedModels::class)]
#[UsesClass(DomainClassifier::class)]
#[UsesClass(RegistrySchemaLoader::class)]
class GeneratedModelsTest extends TestCase
{
    /**
     * The reference map keys each target's URI to its short class name and resolved FQCN, placing a
     * call number in the Common domain and a repository citation in the Source domain.
     */
    #[Test]
    public function referenceMapResolvesTargetsToTheirFullyQualifiedClasses(): void
    {
        $schema = (new RegistrySchemaLoader(dirname(__DIR__, 3) . '/docs/spec/gedcom7-registries'))
            ->load(GedcomVersion::V551);

        $map = GeneratedModels::referenceMap($schema, new DomainClassifier());

        self::assertArrayHasKey('https://gedcom.io/terms/v5.5.1/CALN', $map);
        self::assertSame(
            ['CallNumber', CallNumber::class],
            $map['https://gedcom.io/terms/v5.5.1/CALN'],
        );

        self::assertArrayHasKey('https://gedcom.io/terms/v5.5.1/REPO-XREF_REPO', $map);
        self::assertSame(
            ['RepositoryCitation', RepositoryCitation::class],
            $map['https://gedcom.io/terms/v5.5.1/REPO-XREF_REPO'],
        );
    }
}
