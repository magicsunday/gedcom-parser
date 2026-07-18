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
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use Psr\Http\Message\StreamInterface;

use function dirname;
use function sprintf;

/**
 * Reads a whole GEDCOM stream into a typed {@see GedcomDocument}, choosing the schema version itself.
 *
 * Unlike {@see TypedGedcomParser}, whose version is fixed when it is created, this detects the
 * version from the document's own header ({@see GedcomVersionDetector}) in a single pass: it reads
 * the first record, resolves the version, compiles the matching schema, and maps every recognised
 * record through it. The caller therefore need not know the version up front.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class GedcomDocumentReader
{
    /**
     * @param RegistrySchemaLoader        $schemaLoader    The loader that compiles a version's schema.
     * @param GedcomVersionDetector       $versionDetector The detector resolving the header version.
     * @param array<string, class-string> $recordClasses   The target typed record class per record tag.
     */
    public function __construct(
        private RegistrySchemaLoader $schemaLoader,
        private GedcomVersionDetector $versionDetector,
        private array $recordClasses,
    ) {
    }

    /**
     * Creates a reader over the vendored registry (or a custom path) for the given record-class map.
     *
     * @param array<string, class-string> $recordClasses The target typed record class per record tag.
     * @param string|null                 $registryPath  The registry directory, or NULL for the vendored one.
     *
     * @return self The configured reader.
     */
    public static function create(array $recordClasses, ?string $registryPath = null): self
    {
        $registryPath ??= dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries';

        return new self(
            new RegistrySchemaLoader($registryPath),
            new GedcomVersionDetector(),
            $recordClasses
        );
    }

    /**
     * Reads the stream into a typed aggregate, auto-detecting the schema version from the header.
     *
     * @param StreamInterface $stream   The GEDCOM stream to read.
     * @param int|null        $maxBytes The maximum number of bytes to read before aborting, or NULL
     *                                  for the reader's default.
     *
     * @return GedcomDocument The populated aggregate (empty when the stream carries no records).
     *
     * @throws MappingException When no registry can be compiled for the detected version.
     */
    public function read(StreamInterface $stream, ?int $maxBytes = null): GedcomDocument
    {
        $recordStream = RecordStream::open($stream, $maxBytes);

        if (!$recordStream instanceof RecordStream) {
            return new GedcomDocument();
        }

        // The header is the first record; detect the version from it (or fall back when it is absent).
        $header  = $recordStream->header;
        $version = $this->versionDetector->detect($header);
        $schema  = $this->schemaLoader->load($version);

        // A missing registry compiles to an empty schema; fail loud rather than mapping nothing.
        if ($schema->structures === []) {
            throw new MappingException(
                sprintf(
                    'No GEDCOM registry could be loaded for version "%s".',
                    $version->value
                )
            );
        }

        // The header may declare the place hierarchy FORM once (HEAD.PLAC.FORM); the mapper threads
        // it as the default so places without their own FORM still resolve their jurisdiction labels.
        $mapper     = new GedcomObjectMapper($schema, JsonMapperFactory::fromHeader($header));
        $treeReader = $recordStream->reader;
        $node       = $recordStream->firstRecord;
        $records    = [];

        do {
            $className = $this->recordClasses[$node->tag] ?? null;

            // A record without its cross-reference identifier is malformed: the specification makes
            // the identifier mandatory, so nothing can ever refer to such a record and the model
            // cannot be built from it. Skip it rather than let it abort the whole read and cost
            // every well-formed record in the file. This tests the condition itself rather than
            // catching the failure, so a genuine mapping defect still surfaces loudly.
            if ($node->identifier === null) {
                $node = $treeReader->readRecord();

                continue;
            }

            // A cross-version tag (e.g. a stray 7.0 SNOTE in a 5.5.1 document) is not a record in
            // the detected schema; skip it rather than aborting the whole read.
            if (($className !== null) && $schema->definesRecord($node->tag)) {
                $records[] = $mapper->mapRecord($node, $className);
            }

            $node = $treeReader->readRecord();
        } while ($node instanceof GedcomNode);

        return GedcomDocument::fromRecords($records, ExtensionTagReader::fromHeader($header));
    }
}
