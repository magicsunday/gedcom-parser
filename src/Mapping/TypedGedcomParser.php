<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Mapping;

use Generator;
use MagicSunday\Gedcom\Exception\MappingException;
use MagicSunday\Gedcom\Model\GedcomDocument;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
use MagicSunday\Gedcom\Schema\Schema;
use Psr\Http\Message\StreamInterface;

use function dirname;
use function sprintf;

/**
 * Parses a GEDCOM stream into the typed model.
 *
 * It streams the level-0 records through the {@see GedcomTreeReader} and maps each recognised
 * record — one whose tag has a mapped target class — through the {@see GedcomObjectMapper}, one
 * record at a time so the source is never held in memory as a whole. Records with no mapped class
 * (the `HEAD`/`TRLR` transmission structures, or a record type not modelled) are skipped.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class TypedGedcomParser
{
    /**
     * @param Schema                      $schema        The compiled schema shaping each record node.
     * @param array<string, class-string> $recordClasses The target class per record tag.
     */
    public function __construct(
        private Schema $schema,
        private array $recordClasses,
    ) {
    }

    /**
     * Creates a parser for the given version, wired with the registry schema and the given record
     * class map.
     *
     * @param GedcomVersion               $version       The GEDCOM version whose schema to compile.
     * @param array<string, class-string> $recordClasses The target class per record tag.
     * @param string|null                 $registryPath  The registry directory, or NULL for the vendored one.
     *
     * @throws MappingException When the registry cannot be loaded (no structures compiled)
     */
    public static function create(GedcomVersion $version, array $recordClasses, ?string $registryPath = null): self
    {
        $registryPath ??= dirname(__DIR__, 2) . '/docs/spec/gedcom7-registries';

        $schema = (new RegistrySchemaLoader($registryPath))->load($version);

        // A missing registry compiles to an empty schema; fail loud rather than silently mapping
        // nothing (e.g. if the registry data were ever stripped from the distribution).
        if ($schema->structures === []) {
            throw new MappingException(sprintf('No GEDCOM registry could be loaded from "%s".', $registryPath));
        }

        return new self($schema, $recordClasses);
    }

    /**
     * Parses the stream, yielding the typed record of each recognised level-0 record in document
     * order. Yielding keeps the whole file out of memory: the caller processes one record at a
     * time (wrap in `iterator_to_array()` for the full list).
     *
     * @param StreamInterface $stream   The GEDCOM stream to parse.
     * @param int|null        $maxBytes The maximum number of bytes to read before aborting, or NULL
     *                                  for the reader's default. Lower it when parsing untrusted input.
     *
     * @return Generator<object>
     */
    public function parse(StreamInterface $stream, ?int $maxBytes = null): Generator
    {
        $recordStream = RecordStream::open($stream, $maxBytes);

        if (!$recordStream instanceof RecordStream) {
            return;
        }

        // The streaming generator yields records only; the header-declared extension tags
        // (HEAD.SCHMA) are surfaced by parseDocument(), which builds the whole aggregate.
        yield from $this->mapRecords($recordStream->reader, $recordStream->firstRecord, $recordStream->header);
    }

    /**
     * Parses the stream eagerly into a typed {@see GedcomDocument} aggregate, grouping every
     * recognised record by its modelled type and carrying the header's extension-tag schema. Unlike
     * {@see parse()} this holds the whole document in memory; prefer it when the caller needs random
     * access to the records rather than a single streaming pass.
     *
     * @param StreamInterface $stream   The GEDCOM stream to parse.
     * @param int|null        $maxBytes The maximum number of bytes to read before aborting, or NULL
     *                                  for the reader's default. Lower it when parsing untrusted input.
     *
     * @return GedcomDocument The populated aggregate.
     */
    public function parseDocument(StreamInterface $stream, ?int $maxBytes = null): GedcomDocument
    {
        $recordStream = RecordStream::open($stream, $maxBytes);

        if (!$recordStream instanceof RecordStream) {
            return new GedcomDocument();
        }

        return GedcomDocument::fromRecords(
            $this->mapRecords($recordStream->reader, $recordStream->firstRecord, $recordStream->header),
            ExtensionTagReader::fromHeader($recordStream->header)
        );
    }

    /**
     * Maps the level-0 records from the first node onward, building the mapper from the header so a
     * declared HEAD.PLAC.FORM is threaded as the default place hierarchy.
     *
     * @param GedcomTreeReader $treeReader The reader positioned after the first record.
     * @param GedcomNode       $node       The first record node (the header when present).
     * @param GedcomNode|null  $header     The parsed HEAD record, or NULL when the document has none.
     *
     * @return Generator<object> The typed records in document order.
     *
     * @throws MappingException When a record cannot be mapped.
     */
    private function mapRecords(GedcomTreeReader $treeReader, GedcomNode $node, ?GedcomNode $header): Generator
    {
        $mapper = new GedcomObjectMapper($this->schema, JsonMapperFactory::fromHeader($header));

        do {
            $className = $this->recordClasses[$node->tag] ?? null;

            // A cross-version tag (e.g. a stray 7.0 SNOTE under a 5.5.1 schema) is not a record in
            // the schema; skip it rather than aborting the stream.
            if (($className !== null) && $this->schema->definesRecord($node->tag)) {
                yield $mapper->mapRecord($node, $className);
            }

            $node = $treeReader->readRecord();
        } while ($node instanceof GedcomNode);
    }
}
