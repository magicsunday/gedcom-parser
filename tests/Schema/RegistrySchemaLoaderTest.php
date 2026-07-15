<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Schema;

use MagicSunday\Gedcom\Schema\Cardinality;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use MagicSunday\Gedcom\Schema\StructureDefinition;
use MagicSunday\Gedcom\Schema\Substructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sort;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * Tests compiling the vendored GEDCOM registry into a typed schema, driven by the real
 * registry files rather than a synthetic fixture.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(RegistrySchemaLoader::class)]
#[CoversClass(Schema::class)]
#[CoversClass(StructureDefinition::class)]
#[CoversClass(Substructure::class)]
#[CoversClass(GedcomVersion::class)]
#[UsesClass(Cardinality::class)]
class RegistrySchemaLoaderTest extends TestCase
{
    private const string V551_INDI = 'https://gedcom.io/terms/v5.5.1/record-INDI';

    private const string V70_INDI = 'https://gedcom.io/terms/v7/record-INDI';

    private const string V70_SEX = 'https://gedcom.io/terms/v7/SEX';

    private const string V551_SOUR = 'https://gedcom.io/terms/v5.5.1/record-SOUR';

    /**
     * The temporary registry directory created by a test, removed on tear-down.
     */
    private string $temporaryRegistry = '';

    /**
     * Removes any temporary registry a test created.
     */
    protected function tearDown(): void
    {
        if ($this->temporaryRegistry !== '') {
            self::removeDirectory($this->temporaryRegistry);
            $this->temporaryRegistry = '';
        }
    }

    /**
     * Builds a loader over the vendored registry.
     */
    private static function loader(): RegistrySchemaLoader
    {
        return new RegistrySchemaLoader(dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries');
    }

    /**
     * The 5.5.1 individual record compiles with its tag, an empty payload, and a repeatable
     * BIRT substructure whose cardinality is resolved from the registry token.
     */
    #[Test]
    public function compilesTheV551IndividualRecord(): void
    {
        $indi = self::loader()->load(GedcomVersion::V551)->byUri(self::V551_INDI);

        self::assertInstanceOf(StructureDefinition::class, $indi);
        self::assertSame('INDI', $indi->tag);
        self::assertNull($indi->payload, 'a record structure has no payload');

        $births = $indi->substructuresFor('BIRT');
        self::assertCount(1, $births);

        $birth = $births[0];
        self::assertSame(0, $birth->cardinality->minimum);
        self::assertNull($birth->cardinality->maximum, 'BIRT is {0:M}, so unbounded');
        self::assertTrue($birth->cardinality->isCollection());
        self::assertFalse($birth->cardinality->isRequired());
    }

    /**
     * Data provider for the 5.5.1 tags that split one child tag into an inline-value and a
     * cross-reference-pointer structure, both sharing the tag.
     *
     * @return array<string, array{0: string, 1: string, 2: list<string>}>
     */
    public static function collidingTagProvider(): array
    {
        return [
            // parent record URI, child tag, both variant URIs (alphabetically sorted)
            'INDI NOTE' => [
                self::V551_INDI,
                'NOTE',
                [
                    'https://gedcom.io/terms/v5.5.1/NOTE-SUBMITTER_TEXT_OR_NULL',
                    'https://gedcom.io/terms/v5.5.1/NOTE-XREF_NOTE',
                ],
            ],
            'INDI SOUR' => [
                self::V551_INDI,
                'SOUR',
                [
                    'https://gedcom.io/terms/v5.5.1/SOUR-SOURCE_DESCRIPTION',
                    'https://gedcom.io/terms/v5.5.1/SOUR-XREF_SOUR',
                ],
            ],
            'INDI OBJE' => [
                self::V551_INDI,
                'OBJE',
                [
                    'https://gedcom.io/terms/v5.5.1/OBJE-NULL',
                    'https://gedcom.io/terms/v5.5.1/OBJE-XREF_OBJE',
                ],
            ],
            'SOUR REPO' => [
                self::V551_SOUR,
                'REPO',
                [
                    'https://gedcom.io/terms/v5.5.1/REPO-NULL',
                    'https://gedcom.io/terms/v5.5.1/REPO-XREF_REPO',
                ],
            ],
        ];
    }

    /**
     * When a single child tag maps to more than one structure — every 5.5.1 inline-value versus
     * cross-reference-pointer split (NOTE, SOUR, OBJE, REPO) — both candidates are preserved
     * rather than one silently overwriting the other, and each resolves to its own definition so
     * the mapping layer can distinguish them by payload.
     *
     * @param string       $parentUri    The URI of the parent record.
     * @param string       $tag          The colliding child tag.
     * @param list<string> $expectedUris The URIs of both variants, alphabetically sorted.
     */
    #[Test]
    #[DataProvider('collidingTagProvider')]
    public function preservesEveryVariantOfACollidingChildTag(string $parentUri, string $tag, array $expectedUris): void
    {
        $schema = self::loader()->load(GedcomVersion::V551);
        $parent = $schema->byUri($parentUri);

        self::assertInstanceOf(StructureDefinition::class, $parent);

        $uris = [];

        foreach ($parent->substructuresFor($tag) as $candidate) {
            self::assertInstanceOf(
                StructureDefinition::class,
                $schema->byUri($candidate->uri),
                'each surviving variant resolves to its own definition'
            );

            $uris[] = $candidate->uri;
        }

        sort($uris);

        self::assertSame($expectedUris, $uris, 'both the inline and pointer variants must survive');
    }

    /**
     * The 7.0 SEX structure compiles with its payload type and the enumeration set that
     * constrains it.
     */
    #[Test]
    public function compilesTheV70SexEnumerationStructure(): void
    {
        $sex = self::loader()->load(GedcomVersion::V70)->byUri(self::V70_SEX);

        self::assertInstanceOf(StructureDefinition::class, $sex);
        self::assertSame('SEX', $sex->tag);
        self::assertSame('https://gedcom.io/terms/v7/type-Enum', $sex->payload);
        self::assertSame('https://gedcom.io/terms/v7/enumset-SEX', $sex->enumerationSet);
    }

    /**
     * A substructure referenced by URI in the registry is resolved to its child tag, so the
     * parent exposes it keyed by that tag together with the referenced URI.
     */
    #[Test]
    public function resolvesASubstructureUriToItsChildTag(): void
    {
        $indi = self::loader()->load(GedcomVersion::V70)->byUri(self::V70_INDI);

        self::assertInstanceOf(StructureDefinition::class, $indi);

        $sexes = $indi->substructuresFor('SEX');
        self::assertCount(1, $sexes);

        $sex = $sexes[0];
        self::assertSame(self::V70_SEX, $sex->uri);
        self::assertSame(0, $sex->cardinality->minimum);
        self::assertSame(1, $sex->cardinality->maximum, 'SEX is {0:1} under INDI');
    }

    /**
     * Each version compiles only its own slice: a 5.5.1 schema does not contain 7.0 URIs and
     * vice versa.
     */
    #[Test]
    public function compilesOnlyTheRequestedVersionSlice(): void
    {
        $loader = self::loader();

        self::assertNull($loader->load(GedcomVersion::V551)->byUri(self::V70_INDI), 'no 7.0 URI in the 5.5.1 slice');
        self::assertNull($loader->load(GedcomVersion::V70)->byUri(self::V551_INDI), 'no 5.5.1 URI in the 7.0 slice');
    }

    /**
     * Top-level records (structures with no superstructures) are indexed by tag, while a
     * substructure tag resolves to no record.
     */
    #[Test]
    public function indexesTopLevelRecordsByTag(): void
    {
        $schema = self::loader()->load(GedcomVersion::V551);

        $individual = $schema->recordByTag('INDI');
        self::assertInstanceOf(StructureDefinition::class, $individual);
        self::assertSame(self::V551_INDI, $individual->uri);

        self::assertInstanceOf(StructureDefinition::class, $schema->recordByTag('SUBM'));
        self::assertNull($schema->recordByTag('DATE'), 'a substructure tag is not a top-level record');
        self::assertNull($schema->recordByTag('ZZZZ'), 'an unknown tag has no record');
    }

    /**
     * The transmission structures (HEAD, TRLR) and the serialization pseudo-structures (CONT),
     * which also have no superstructures, are not indexed as data records.
     */
    #[Test]
    public function doesNotIndexTransmissionOrPseudoStructuresAsRecords(): void
    {
        $schema = self::loader()->load(GedcomVersion::V70);

        self::assertInstanceOf(StructureDefinition::class, $schema->recordByTag('INDI'));
        self::assertInstanceOf(StructureDefinition::class, $schema->recordByTag('SNOTE'));

        self::assertNull($schema->recordByTag('CONT'), 'CONT is a serialization pseudo-structure, not a record');
        self::assertNull($schema->recordByTag('HEAD'), 'HEAD is a transmission structure, not a data record');
        self::assertNull($schema->recordByTag('TRLR'), 'TRLR is a transmission structure, not a data record');
    }

    /**
     * Files that are not usable structure definitions — a non-mapping document and one missing
     * its tag — are skipped, and a substructure whose URI has no structure in the slice is
     * dropped, while the valid structures still compile.
     */
    #[Test]
    public function skipsUnusableFilesAndUnresolvableSubstructures(): void
    {
        $registry = $this->writeTemporaryRegistry([
            // A valid structure referencing one resolvable and one dangling substructure.
            'foo.yaml' => "uri: https://gedcom.io/terms/v7/FOO\n"
                . "standard tag: 'FOO'\n"
                . "payload: null\n"
                . "substructures:\n"
                . "  \"https://gedcom.io/terms/v7/BAR\": \"{0:1}\"\n"
                . "  \"https://gedcom.io/terms/v7/GHOST\": \"{0:M}\"\n",
            'bar.yaml' => "uri: https://gedcom.io/terms/v7/BAR\nstandard tag: 'BAR'\npayload: null\n",
            // A scalar document (not a mapping) and a mapping without a standard tag.
            'scalar.yaml' => "just a plain scalar\n",
            'notag.yaml'  => "uri: https://gedcom.io/terms/v7/BAZ\npayload: null\n",
        ]);

        $schema = (new RegistrySchemaLoader($registry))->load(GedcomVersion::V70);

        self::assertCount(2, $schema->structures, 'only the two valid structures compile');
        self::assertNull($schema->byUri('https://gedcom.io/terms/v7/BAZ'), 'the tag-less file is skipped');

        $foo = $schema->byUri('https://gedcom.io/terms/v7/FOO');
        self::assertInstanceOf(StructureDefinition::class, $foo);
        self::assertCount(1, $foo->substructuresFor('BAR'), 'the resolvable substructure is kept');
        self::assertSame([], $foo->substructuresFor('GHOST'), 'the dangling substructure is dropped');
    }

    /**
     * A registry path without a structure directory compiles to an empty schema instead of
     * failing.
     */
    #[Test]
    public function compilesAnEmptySchemaWhenTheStructureDirectoryIsAbsent(): void
    {
        $registry = $this->writeTemporaryRegistry([]);

        $schema = (new RegistrySchemaLoader($registry . '/does-not-exist'))->load(GedcomVersion::V70);

        self::assertSame([], $schema->structures);
    }

    /**
     * Creates a temporary registry directory containing the given `structure/standard` files.
     *
     * @param array<string, string> $files A map of file name to YAML content.
     *
     * @return string The temporary registry base path.
     */
    private function writeTemporaryRegistry(array $files): string
    {
        $base      = sys_get_temp_dir() . '/gedcom-schema-' . uniqid('', true);
        $directory = $base . '/structure/standard';

        // Record the base for tear-down before creating anything, so a failure midway through
        // still leaves the directory to be cleaned up.
        $this->temporaryRegistry = $base;

        mkdir($directory, 0o777, true);

        foreach ($files as $name => $content) {
            file_put_contents($directory . '/' . $name, $content);
        }

        return $base;
    }

    /**
     * Recursively removes a directory and its contents.
     *
     * @param string $directory The directory to remove.
     */
    private static function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $entries = scandir($directory);

        foreach ($entries === false ? [] : $entries as $entry) {
            if ($entry === '.') {
                continue;
            }

            if ($entry === '..') {
                continue;
            }

            $path = $directory . '/' . $entry;

            if (is_dir($path)) {
                self::removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
