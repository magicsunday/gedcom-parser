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
use MagicSunday\Gedcom\Schema\GedcomVersion;

use function str_starts_with;
use function trim;

/**
 * Determines which GEDCOM version a document is written in from its header.
 *
 * The version lives in the header's `GEDC.VERS` line (`5.5.1`, `7.0`, `7.0.14`, …). This resolves
 * it to a {@see GedcomVersion}, treating any 7.x value as GEDCOM 7.0 and defaulting to 5.5.1 — the
 * historical baseline — when the header, the `GEDC` substructure or the `VERS` line is absent or
 * unrecognised, so a version-less or malformed header still parses rather than failing.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class GedcomVersionDetector
{
    /**
     * The `GEDC` header substructure tag holding the GEDCOM metadata.
     */
    private const string TAG_GEDC = 'GEDC';

    /**
     * The `VERS` tag under `GEDC` holding the version string.
     */
    private const string TAG_VERS = 'VERS';

    /**
     * Detects the GEDCOM version from a parsed HEAD record node.
     *
     * @param GedcomNode|null $header The parsed HEAD record, or NULL when the document has none
     *
     * @return GedcomVersion The detected version, defaulting to 5.5.1
     */
    public function detect(?GedcomNode $header): GedcomVersion
    {
        if (!$header instanceof GedcomNode) {
            return GedcomVersion::V551;
        }

        $gedc = $this->firstChild($header, self::TAG_GEDC);

        if (!$gedc instanceof GedcomNode) {
            return GedcomVersion::V551;
        }

        $vers = $this->firstChild($gedc, self::TAG_VERS);

        return $this->fromVersionString($vers?->value);
    }

    /**
     * Maps a raw `GEDC.VERS` value to a typed version, defaulting to 5.5.1.
     *
     * @param string|null $version The raw VERS value, or NULL when absent
     *
     * @return GedcomVersion The matching version
     */
    private function fromVersionString(?string $version): GedcomVersion
    {
        if ($version === null) {
            return GedcomVersion::V551;
        }

        $trimmed = trim($version);
        $exact   = GedcomVersion::tryFrom($trimmed);

        if ($exact instanceof GedcomVersion) {
            return $exact;
        }

        // Any 7.x version (a 7.0 patch level such as 7.0.14, or a future 7.x minor) is mapped to the
        // 7.0 schema — the only 7-series version modelled. The `7.` prefix requires the dotted form,
        // so a malformed value like `70` or `7-draft` correctly falls back to 5.5.1 rather than 7.0.
        if (str_starts_with($trimmed, '7.')) {
            return GedcomVersion::V70;
        }

        return GedcomVersion::V551;
    }

    /**
     * Returns the first direct child of a node carrying the given tag, or NULL when none does.
     *
     * @param GedcomNode $node The node whose children to search
     * @param string     $tag  The tag to look for
     *
     * @return GedcomNode|null The first matching child, or NULL
     */
    private function firstChild(GedcomNode $node, string $tag): ?GedcomNode
    {
        foreach ($node->children as $child) {
            if ($child->tag === $tag) {
                return $child;
            }
        }

        return null;
    }
}
