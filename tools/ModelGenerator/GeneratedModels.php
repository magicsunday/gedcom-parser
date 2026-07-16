<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

use MagicSunday\Gedcom\Schema\GedcomVersion;

/**
 * The manifest of registry structures that are generated into committed model classes. It is the
 * single source of truth shared by the generation driver and the freshness gate: the driver writes
 * each target's class, and the freshness test re-generates each and asserts it still matches the
 * committed file, so a committed generated class can never drift from the registry + generator.
 *
 * The roll-out grows this list, structure by structure, towards full coverage.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class GeneratedModels
{
    /**
     * Private constructor; this is a static-only manifest.
     */
    private function __construct()
    {
    }

    /**
     * The generation targets.
     *
     * @return list<array{version: GedcomVersion, uri: string, class: string, isRecord: bool, description: string}>
     */
    public static function targets(): array
    {
        return [
            [
                'version'     => GedcomVersion::V551,
                'uri'         => 'https://gedcom.io/terms/v5.5.1/SOUR-XREF_SOUR-DATA',
                'class'       => 'SourceCitationData',
                'isRecord'    => false,
                'description' => 'The data (transcribed text and its date) of a source citation.',
            ],
        ];
    }
}
