<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Mapping;

use MagicSunday\Gedcom\Parse\GedcomNode;

use function count;
use function preg_split;
use function trim;

/**
 * Reads the GEDCOM 7.0 extension-tag schema declared in the header.
 *
 * A 7.0 document documents each custom (underscore-prefixed) tag it uses by mapping it to a URI in
 * a `HEAD.SCHMA.TAG` line, whose payload is the extension tag followed by the delimiter and the URI
 * (`2 TAG _LOC https://example.com/loc`). This reader turns those declarations into a
 * tag-to-URI map. A 5.5.1 header carries no `SCHMA`, so the map is empty for it.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class ExtensionTagReader
{
    /**
     * The header substructure carrying the extension-tag declarations.
     */
    private const string TAG_SCHMA = 'SCHMA';

    /**
     * The declaration tag mapping one extension tag to its URI.
     */
    private const string TAG_TAG = 'TAG';

    /**
     * Static-only utility; use {@see fromHeader()}.
     */
    private function __construct()
    {
    }

    /**
     * Reads the `HEAD.SCHMA.TAG` declarations into a map of extension tag to documented URI.
     *
     * A value-less or malformed `TAG` payload (no URI part) is skipped. GEDCOM 7.0 permits the same
     * extension tag to be declared more than once with different URIs (disambiguated downstream by
     * the structure it appears under), so every declared URI is preserved in document order rather
     * than collapsed to the last.
     *
     * @param GedcomNode|null $header The parsed HEAD record, or NULL when the document has none.
     *
     * @return array<string, list<string>> The extension tag mapped to its declared URIs, empty when
     *                                     none is declared.
     */
    public static function fromHeader(?GedcomNode $header): array
    {
        $schema = $header?->firstChild(self::TAG_SCHMA);

        if (!$schema instanceof GedcomNode) {
            return [];
        }

        $extensionTags = [];

        foreach ($schema->children as $child) {
            if ($child->tag !== self::TAG_TAG) {
                continue;
            }

            if ($child->value === null) {
                continue;
            }

            $parts = preg_split('/\s+/', trim($child->value), 2);

            // A conformant TAG payload is `extTag <delim> URI`; skip anything without both parts.
            if ($parts === false) {
                continue;
            }

            if (count($parts) !== 2) {
                continue;
            }

            // `\s` matches a form-feed that trim() leaves in place, so a value bracketed by one can
            // still split to an empty tag or URI; skip those rather than storing a blank entry.
            if ($parts[0] === '') {
                continue;
            }

            if ($parts[1] === '') {
                continue;
            }

            $extensionTags[$parts[0]][] = $parts[1];
        }

        return $extensionTags;
    }
}
