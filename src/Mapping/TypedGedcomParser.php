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
use MagicSunday\Gedcom\Reader;
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
     * The header record tag whose HEAD.PLAC.FORM declares the default place hierarchy.
     */
    private const string TAG_HEAD = 'HEAD';

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
     * @param StreamInterface $stream The GEDCOM stream to parse.
     *
     * @return Generator<object>
     */
    public function parse(StreamInterface $stream): Generator
    {
        $treeReader = new GedcomTreeReader(new Reader($stream));
        $node       = $treeReader->readRecord();

        if (!$node instanceof GedcomNode) {
            return;
        }

        // Build the mapper from the header (the first record) so a place hierarchy declared once as
        // HEAD.PLAC.FORM is threaded as the default for every place that carries none of its own.
        $header = $node->tag === self::TAG_HEAD ? $node : null;
        $mapper = new GedcomObjectMapper($this->schema, JsonMapperFactory::fromHeader($header));

        do {
            $className = $this->recordClasses[$node->tag] ?? null;

            if ($className !== null) {
                yield $mapper->mapRecord($node, $className);
            }

            $node = $treeReader->readRecord();
        } while ($node instanceof GedcomNode);
    }

    /**
     * Parses the stream eagerly into a typed {@see GedcomDocument} aggregate, grouping every
     * recognised record by its modelled type. Unlike {@see parse()} this holds the whole document
     * in memory; prefer it when the caller needs random access to the records rather than a single
     * streaming pass.
     *
     * @param StreamInterface $stream The GEDCOM stream to parse.
     *
     * @return GedcomDocument The populated aggregate.
     */
    public function parseDocument(StreamInterface $stream): GedcomDocument
    {
        return GedcomDocument::fromRecords($this->parse($stream));
    }
}
