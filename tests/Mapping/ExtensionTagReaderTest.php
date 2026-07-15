<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Mapping;

use MagicSunday\Gedcom\Mapping\ExtensionTagReader;
use MagicSunday\Gedcom\Parse\GedcomNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests reading the GEDCOM 7.0 HEAD.SCHMA.TAG extension-tag declarations into a tag-to-URI map.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(ExtensionTagReader::class)]
#[UsesClass(GedcomNode::class)]
class ExtensionTagReaderTest extends TestCase
{
    /**
     * Builds a HEAD node carrying a SCHMA structure with the given TAG child values.
     *
     * @param list<string|null> $tagValues The raw value of each `TAG` line under `SCHMA`.
     *
     * @return GedcomNode The synthetic header node.
     */
    private static function headerWithTags(array $tagValues): GedcomNode
    {
        $tags = [];

        foreach ($tagValues as $value) {
            $tags[] = new GedcomNode(2, 'TAG', null, null, $value);
        }

        return new GedcomNode(0, 'HEAD', null, null, null, [
            new GedcomNode(1, 'SCHMA', null, null, null, $tags),
        ]);
    }

    /**
     * A well-formed set of TAG declarations maps each extension tag to its URI, and the delimiter is
     * a whitespace run (one or more spaces) so an over-spaced payload still splits cleanly.
     */
    #[Test]
    public function fromHeaderMapsEachDeclaredExtensionTagToItsUri(): void
    {
        $header = self::headerWithTags([
            '_LOC https://gedcom.io/terms/v7/LOC',
            '_MEDI   https://example.com/media',
        ]);

        self::assertSame(
            [
                '_LOC'  => ['https://gedcom.io/terms/v7/LOC'],
                '_MEDI' => ['https://example.com/media'],
            ],
            ExtensionTagReader::fromHeader($header),
        );
    }

    /**
     * GEDCOM 7.0 permits the same extension tag to be declared more than once with different URIs;
     * every declared URI is preserved in document order rather than collapsed to the last.
     */
    #[Test]
    public function fromHeaderPreservesEveryUriOfADuplicateTag(): void
    {
        $header = self::headerWithTags([
            '_LOC https://example.com/first',
            '_LOC https://example.com/second',
        ]);

        self::assertSame(
            ['_LOC' => ['https://example.com/first', 'https://example.com/second']],
            ExtensionTagReader::fromHeader($header),
        );
    }

    /**
     * A malformed or value-less TAG line — one with no URI part, or none at all — is skipped rather
     * than producing a half-populated entry, while the well-formed siblings still map.
     */
    #[Test]
    public function fromHeaderSkipsMalformedTagDeclarations(): void
    {
        $header = self::headerWithTags([
            '_LOC https://example.com/loc',
            '_NOURL',
            null,
            '   ',
            // A form-feed survives trim() but splits under `\s`, so these would otherwise yield a
            // blank tag or a blank URI.
            "\f_LEADINGFF https://example.com/x",
            "_TRAILINGFF\f",
        ]);

        self::assertSame(['_LOC' => ['https://example.com/loc']], ExtensionTagReader::fromHeader($header));
    }

    /**
     * A non-TAG child under SCHMA (an unexpected substructure) is ignored, while the sibling TAG
     * declarations still map.
     */
    #[Test]
    public function fromHeaderIgnoresNonTagChildrenUnderSchema(): void
    {
        $header = new GedcomNode(0, 'HEAD', null, null, null, [
            new GedcomNode(1, 'SCHMA', null, null, null, [
                new GedcomNode(2, 'NOTE', null, null, 'a stray note under SCHMA'),
                new GedcomNode(2, 'TAG', null, null, '_LOC https://example.com/loc'),
            ]),
        ]);

        self::assertSame(['_LOC' => ['https://example.com/loc']], ExtensionTagReader::fromHeader($header));
    }

    /**
     * A header without a SCHMA structure (a 5.5.1 document) yields an empty map, as does an absent
     * header.
     */
    #[Test]
    public function fromHeaderReturnsAnEmptyMapWithoutASchema(): void
    {
        self::assertSame([], ExtensionTagReader::fromHeader(new GedcomNode(0, 'HEAD', null, null, null)));
        self::assertSame([], ExtensionTagReader::fromHeader(null));
    }
}
