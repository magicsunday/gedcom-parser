<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PSR-7 stream decorator, focusing on the guarded edge cases of the byte-level API.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(Stream::class)]
class StreamTest extends TestCase
{
    /**
     * Builds an in-memory stream primed with the given content and rewound to the start.
     *
     * @param string $content The content to seed the stream with.
     *
     * @return Stream The rewound, readable stream.
     */
    private static function seededStream(string $content): Stream
    {
        $stream = new Stream('php://memory', 'r+');
        $stream->write($content);
        $stream->rewind();

        return $stream;
    }

    /**
     * A positive length reads that many bytes off the front of the stream.
     */
    #[Test]
    public function readReturnsTheRequestedNumberOfBytes(): void
    {
        self::assertSame('hel', self::seededStream('hello')->read(3));
    }

    /**
     * A zero length yields an empty string without advancing the cursor, so the next read still sees
     * the full content.
     */
    #[Test]
    public function readReturnsAnEmptyStringForAZeroLength(): void
    {
        $stream = self::seededStream('hello');

        self::assertSame('', $stream->read(0));
        self::assertSame('hello', $stream->read(5));
    }

    /**
     * A negative length is treated as "no bytes available" and yields an empty string, rather than
     * letting it reach fread() where PHP 8 raises a ValueError.
     */
    #[Test]
    public function readReturnsAnEmptyStringForANegativeLength(): void
    {
        self::assertSame('', self::seededStream('hello')->read(-1));
    }

    /**
     * getMetadata() with no key returns the whole associative array; a known key returns its value.
     */
    #[Test]
    public function getMetadataReturnsTheWholeArrayOrASpecificKey(): void
    {
        $stream = self::seededStream('');

        self::assertIsArray($stream->getMetadata());
        self::assertSame('php://memory', $stream->getMetadata('uri'));
        self::assertNull($stream->getMetadata('does-not-exist'));
    }
}
