<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Parse;

use MagicSunday\Gedcom\Parse\GedcomNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the generic parse-tree node, focusing on its direct-child lookup.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(GedcomNode::class)]
class GedcomNodeTest extends TestCase
{
    /**
     * Builds a value-less child node bearing the given tag.
     *
     * @param string      $tag   The child's tag.
     * @param string|null $value The child's literal value, or NULL.
     *
     * @return GedcomNode The child node.
     */
    private static function child(string $tag, ?string $value = null): GedcomNode
    {
        return new GedcomNode(1, $tag, null, null, $value);
    }

    /**
     * firstChild() returns the first direct child bearing the requested tag.
     */
    #[Test]
    public function firstChildReturnsTheMatchingChild(): void
    {
        $node = new GedcomNode(0, 'HEAD', null, null, null, [
            self::child('GEDC'),
            self::child('CHAR', 'UTF-8'),
        ]);

        $child = $node->firstChild('CHAR');

        self::assertNotNull($child);
        self::assertSame('CHAR', $child->tag);
        self::assertSame('UTF-8', $child->value);
    }

    /**
     * firstChild() returns NULL when no direct child bears the tag.
     */
    #[Test]
    public function firstChildReturnsNullWhenAbsent(): void
    {
        $node = new GedcomNode(0, 'HEAD', null, null, null, [self::child('GEDC')]);

        self::assertNull($node->firstChild('PLAC'));
    }

    /**
     * firstChild() returns the first match in document order when a tag repeats.
     */
    #[Test]
    public function firstChildReturnsTheFirstOfSeveralMatches(): void
    {
        $node = new GedcomNode(0, 'INDI', null, null, null, [
            self::child('NAME', 'John /Doe/'),
            self::child('NAME', 'Johnny /Doe/'),
        ]);

        self::assertSame('John /Doe/', $node->firstChild('NAME')?->value);
    }

    /**
     * firstChild() only searches direct children, not deeper descendants.
     */
    #[Test]
    public function firstChildDoesNotDescendIntoGrandchildren(): void
    {
        $node = new GedcomNode(0, 'HEAD', null, null, null, [
            new GedcomNode(1, 'PLAC', null, null, null, [self::child('FORM', 'City')]),
        ]);

        self::assertNull($node->firstChild('FORM'));
        self::assertSame('City', $node->firstChild('PLAC')?->firstChild('FORM')?->value);
    }
}
