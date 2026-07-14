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
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\Parse\GedcomTreeReader;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Schema\GedcomVersion;
use MagicSunday\Gedcom\Schema\RegistrySchemaLoader;
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
     * @param GedcomObjectMapper          $mapper        The mapper building a typed record from a node
     * @param array<string, class-string> $recordClasses The target class per record tag
     */
    public function __construct(
        private GedcomObjectMapper $mapper,
        private array $recordClasses,
    ) {
    }

    /**
     * Creates a parser for the given version, wired with the registry schema and the given record
     * class map.
     *
     * @param GedcomVersion               $version       The GEDCOM version whose schema to compile
     * @param array<string, class-string> $recordClasses The target class per record tag
     * @param string|null                 $registryPath  The registry directory, or NULL for the vendored one
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

        return new self(new GedcomObjectMapper($schema, JsonMapperFactory::create()), $recordClasses);
    }

    /**
     * Parses the stream, yielding the typed record of each recognised level-0 record in document
     * order. Yielding keeps the whole file out of memory: the caller processes one record at a
     * time (wrap in `iterator_to_array()` for the full list).
     *
     * @param StreamInterface $stream The GEDCOM stream to parse
     *
     * @return Generator<object>
     */
    public function parse(StreamInterface $stream): Generator
    {
        $treeReader = new GedcomTreeReader(new Reader($stream));

        while (($node = $treeReader->readRecord()) instanceof GedcomNode) {
            $className = $this->recordClasses[$node->tag] ?? null;

            if ($className === null) {
                continue;
            }

            yield $this->mapper->mapRecord($node, $className);
        }
    }
}
