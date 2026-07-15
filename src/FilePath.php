<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use function explode;
use function in_array;
use function parse_url;
use function preg_match;
use function rawurldecode;
use function str_contains;
use function str_starts_with;

/**
 * Classifies an `OBJE.FILE` payload (a GEDCOM 7.0 `FilePath`) and resolves it to the name of the
 * GEDZIP archive entry it addresses.
 *
 * GEDCOM 7.0 restricts a `FilePath` to a web URL (`http`/`https`/`ftp`), a `file:` URL, or a
 * scheme-less relative reference. Only the last form addresses a file embedded in a GEDZIP archive;
 * per the specification the archive entry then bears "the same zip file name as the payload", but
 * because archive names are not percent-escaped while a `FilePath` is, the payload must be
 * percent-decoded to obtain the entry name. This class is the security boundary: it rejects any
 * absolute path, parent-directory (`..`) traversal, backslash or query/fragment — in raw or escaped
 * form — so a hostile reference cannot address anything outside the archive.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class FilePath
{
    /**
     * Private constructor; use {@see toArchiveEntry()}.
     */
    private function __construct()
    {
    }

    /**
     * Resolves an `OBJE.FILE` payload to the case-sensitive UTF-8 name of the GEDZIP entry it
     * addresses, or NULL when the payload does not name an embedded file — a web or `file:` URL, an
     * absolute path, a parent-directory traversal, a backslash, or a query/fragment.
     *
     * @param string $payload The `OBJE.FILE` line value (a GEDCOM 7.0 FilePath).
     *
     * @return string|null The archive entry name, or NULL when the payload is not an embedded-file reference.
     */
    public static function toArchiveEntry(string $payload): ?string
    {
        if ($payload === '') {
            return null;
        }

        // Classify on the raw (still percent-escaped) payload: an escaped delimiter such as `%23`
        // is a literal character in a filename, not a URL delimiter, so the scheme/query/fragment
        // tests must run before decoding. A scheme means a web or `file:` URL (not embedded); a
        // malformed URL (parse_url returns false) is likewise not an embedded reference.
        $parts = parse_url($payload);

        if ($parts === false) {
            return null;
        }

        if (
            isset($parts['scheme'])
            || isset($parts['query'])
            || isset($parts['fragment'])
        ) {
            return null;
        }

        // Archive names are not percent-escaped, so decode the payload to the actual entry name. The
        // remaining boundary checks all run on the DECODED string so an escaped separator, traversal
        // or banned character cannot slip through: `%2F` decodes to `/`, `%2E%2E` to `..`, `%5C` to
        // `\`, `%00` to a null byte. GEDCOM uses URL percent-encoding, where `+` is a literal plus,
        // so decode with rawurldecode rather than urldecode.
        $entry = rawurldecode($payload);

        if (str_starts_with($entry, '/')) {
            return null;
        }

        if (str_contains($entry, '\\')) {
            return null;
        }

        if (in_array('..', explode('/', $entry), true)) {
            return null;
        }

        // Reject the GEDCOM "banned" characters — the C0 controls except tab/CR/LF, DEL and the C1
        // controls — and any non-UTF-8 payload (preg_match returns false), since a conformant
        // archive name is UTF-8 text without such characters; a null byte or control character in a
        // path is never a legitimate embedded-file reference.
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\x{0080}-\x{009F}]/u', $entry) !== 0) {
            return null;
        }

        return $entry;
    }
}
