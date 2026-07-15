<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Architecture;

use MagicSunday\Gedcom\GedcomArchive;
use MagicSunday\Gedcom\GedcomZipReader;
use MagicSunday\Gedcom\Parser;
use MagicSunday\Gedcom\Reader;
use MagicSunday\Gedcom\Stream;
use MagicSunday\Gedcom\StreamFactory;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Declarative layer boundaries enforced by phpat as part of the PHPStan run.
 *
 * The pipeline is layered top-down: the public Parser entry point orchestrates the schema-driven
 * Mapping hub, which reads bytes through the low-level I/O primitives (Reader/Stream/StreamFactory)
 * and drives the generic node-tree reader (Parse), the per-version Schema, the immutable Model
 * records and their parsed ValueObject leaves. Encoding and Exception are shared leaves. Dependencies
 * may only point downward — no lower layer may depend back on the entry point — and these rules pin
 * that.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ArchitectureTest
{
    /**
     * The namespace prefix shared by every class in the library.
     */
    private const string NS = 'MagicSunday\\Gedcom\\';

    /**
     * Value objects are parsed leaf values; they must not reach up into mapping, schema, records,
     * encoding or the I/O layer.
     *
     * @return Rule The architecture rule.
     */
    public function testValueObjectsAreAPureLeaf(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::NS . 'ValueObject'))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::inNamespace(self::NS . 'Mapping'),
                Selector::inNamespace(self::NS . 'Parse'),
                Selector::inNamespace(self::NS . 'Schema'),
                Selector::inNamespace(self::NS . 'Model'),
                Selector::inNamespace(self::NS . 'Encoding'),
                ...$this->entryPoints(),
            )
            ->because('value objects are parsed leaves with no knowledge of mapping, schema, records or I/O');
    }

    /**
     * Immutable records may hold value objects, but must not depend on the mapper that builds them,
     * the schema, the tree reader, encoding or the I/O layer.
     *
     * @return Rule The architecture rule.
     */
    public function testRecordsDependOnlyOnValueObjects(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::NS . 'Model'))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::inNamespace(self::NS . 'Mapping'),
                Selector::inNamespace(self::NS . 'Parse'),
                Selector::inNamespace(self::NS . 'Schema'),
                Selector::inNamespace(self::NS . 'Encoding'),
                ...$this->entryPoints(),
            )
            ->because('records are immutable data holding only value objects, not knowledge of how they are built');
    }

    /**
     * The ANSEL decoder is a self-contained leaf and must not depend on any higher layer.
     *
     * @return Rule The architecture rule.
     */
    public function testEncodingIsAPureLeaf(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::NS . 'Encoding'))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::inNamespace(self::NS . 'Mapping'),
                Selector::inNamespace(self::NS . 'Parse'),
                Selector::inNamespace(self::NS . 'Schema'),
                Selector::inNamespace(self::NS . 'Model'),
                Selector::inNamespace(self::NS . 'ValueObject'),
                ...$this->entryPoints(),
            )
            ->because('the ANSEL decoder is a self-contained byte-level converter with no upward dependencies');
    }

    /**
     * Domain exceptions are shared leaves and must not depend on any other layer.
     *
     * @return Rule The architecture rule.
     */
    public function testExceptionsAreAPureLeaf(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::NS . 'Exception'))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::inNamespace(self::NS . 'Mapping'),
                Selector::inNamespace(self::NS . 'Parse'),
                Selector::inNamespace(self::NS . 'Schema'),
                Selector::inNamespace(self::NS . 'Model'),
                Selector::inNamespace(self::NS . 'ValueObject'),
                Selector::inNamespace(self::NS . 'Encoding'),
                ...$this->entryPoints(),
            )
            ->because('domain exceptions are shared leaves carrying only structured context, not other layers');
    }

    /**
     * The generic node-tree reader sits below the schema-driven mapper; it may read via the line
     * tokeniser but must not depend on mapping, schema, records, value objects or encoding.
     *
     * @return Rule The architecture rule.
     */
    public function testTheTreeReaderDoesNotDependOnTheMappingLayer(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::NS . 'Parse'))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::inNamespace(self::NS . 'Mapping'),
                Selector::inNamespace(self::NS . 'Schema'),
                Selector::inNamespace(self::NS . 'Model'),
                Selector::inNamespace(self::NS . 'ValueObject'),
                Selector::inNamespace(self::NS . 'Encoding'),
            )
            ->because('the generic node-tree reader is agnostic of the schema-driven typed mapping above it');
    }

    /**
     * The per-version schema is a declarative description; it must not depend on I/O, the mapper,
     * the tree reader, records or value objects.
     *
     * @return Rule The architecture rule.
     */
    public function testTheSchemaDoesNotDependOnIoOrMapping(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::NS . 'Schema'))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::inNamespace(self::NS . 'Mapping'),
                Selector::inNamespace(self::NS . 'Parse'),
                Selector::inNamespace(self::NS . 'Model'),
                Selector::inNamespace(self::NS . 'ValueObject'),
                Selector::inNamespace(self::NS . 'Encoding'),
                ...$this->entryPoints(),
            )
            ->because('the schema is a declarative per-version description, decoupled from I/O and the mapper');
    }

    /**
     * The public Parser is the entry point that assembles the pipeline; no lower layer — neither the
     * Mapping hub nor the generic tree reader — may depend back on it, which would close a cycle.
     *
     * @return Rule The architecture rule.
     */
    public function testNothingDependsOnThePublicEntryPoint(): Rule
    {
        return PHPat::rule()
            ->classes(
                Selector::inNamespace(self::NS . 'Mapping'),
                Selector::inNamespace(self::NS . 'Parse'),
            )
            ->shouldNot()
            ->dependOn()
            ->classes(Selector::classname(Parser::class))
            ->because('the public Parser entry point orchestrates the pipeline; no lower layer may depend back on it');
    }

    /**
     * The low-level I/O and entry-point classes at the root namespace (the line tokeniser, the PSR-7
     * stream, its factory and the public parser).
     *
     * @return list<SelectorInterface> The selectors matching each root-level I/O and entry class.
     */
    private function entryPoints(): array
    {
        return [
            Selector::classname(Reader::class),
            Selector::classname(Stream::class),
            Selector::classname(StreamFactory::class),
            Selector::classname(Parser::class),
            Selector::classname(GedcomZipReader::class),
            Selector::classname(GedcomArchive::class),
        ];
    }
}
