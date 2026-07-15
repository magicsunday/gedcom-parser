<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test;

use MagicSunday\Gedcom\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the FilePath classifier: it maps an `OBJE.FILE` payload to its GEDZIP archive entry name, or
 * rejects it (returns NULL) when the payload is a web/`file:` URL, absolute, a traversal, a
 * backslash path, or carries a query/fragment.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(FilePath::class)]
class FilePathTest extends TestCase
{
    /**
     * Provides FilePath payloads and the archive entry each resolves to (or NULL when rejected).
     *
     * @return array<string, array{0: string, 1: string|null}>
     */
    public static function payloadProvider(): array
    {
        return [
            // Embedded-file references — resolve to the (percent-decoded) entry name.
            'relative path in a media directory'         => ['media/pixel.png', 'media/pixel.png'],
            'bare relative filename'                     => ['portrait.jpg', 'portrait.jpg'],
            'percent-escaped path and character'         => ['media%2Fpi%78el.png', 'media/pixel.png'],
            'literal hash via escape'                    => ['file%23name.png', 'file#name.png'],
            'dots inside a filename, not a segment'      => ['media/pixel..png', 'media/pixel..png'],
            'double-encoded is a literal, not traversal' => ['media%252F..%252Fsecret', 'media%2F..%2Fsecret'],
            'accented UTF-8 filename accepted'           => ['café.png', 'café.png'],

            // Web and file URLs — external, not embedded.
            'http URL'                  => ['http://example.com/x.jpg', null],
            'https URL'                 => ['https://example.com/x.jpg', null],
            'ftp URL'                   => ['ftp://example.com/x.jpg', null],
            'file URL'                  => ['file:///etc/passwd', null],
            'scheme-like first segment' => ['scheme:opaque', null],

            // Non-conformant references — rejected by the security boundary.
            'empty payload'                   => ['', null],
            'absolute path'                   => ['/etc/passwd', null],
            'escaped absolute path'           => ['%2Fetc%2Fpasswd', null],
            'escaped protocol-relative'       => ['%2F%2Fhost/x.jpg', null],
            'parent traversal'                => ['../secret', null],
            'nested traversal'                => ['a/../../b', null],
            'escaped traversal'               => ['media%2F..%2Fsecret', null],
            'backslash path'                  => ['media\\pixel.png', null],
            'escaped backslash'               => ['media%5Csecret', null],
            'null byte injection'             => ['media%00pixel.png', null],
            'control character'               => ['media%01.png', null],
            'non-utf8 byte'                   => ['%FF.png', null],
            'malformed url (parse_url false)' => ['//host:port', null],
            'query string'                    => ['media/pixel.png?raw=1', null],
            'fragment'                        => ['media/pixel.png#frag', null],
        ];
    }

    /**
     * Each payload resolves to its embedded archive entry name, or is rejected with NULL.
     *
     * @param string      $payload  The OBJE.FILE payload.
     * @param string|null $expected The resolved entry name, or NULL when rejected.
     */
    #[DataProvider('payloadProvider')]
    #[Test]
    public function classifiesTheFilePathPayload(string $payload, ?string $expected): void
    {
        self::assertSame($expected, FilePath::toArchiveEntry($payload));
    }
}
