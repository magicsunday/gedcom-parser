<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Tools\ModelGenerator;

/**
 * Decides the target namespace of a generated class: a level-0 record goes under `Model\Record`,
 * and a substructure under `Model\Substructure\<Domain>`, where the domain comes from an explicit,
 * reviewed tag-to-domain table (defaulting to `Common`). The classification is deterministic and
 * pinned by a table so a re-run never churns a class's file path.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class DomainClassifier
{
    /**
     * The model layer's base namespace.
     */
    private const string BASE = 'MagicSunday\\Gedcom\\Model';

    /**
     * The substructure domain per GEDCOM tag. A tag absent here defaults to `Common`.
     *
     * @var array<string, string>
     */
    private const array DOMAINS = [
        // Source and repository citations.
        'SOUR' => 'Source',
        'DATA' => 'Source',
        'REPO' => 'Source',
        // Place structures.
        'PLAC' => 'Place',
        'MAP'  => 'Place',
        // Personal name structures.
        'NAME' => 'Name',
        'FONE' => 'Name',
        'ROMN' => 'Name',
        // Events.
        'BIRT' => 'Event',
        'CHR'  => 'Event',
        'DEAT' => 'Event',
        'BURI' => 'Event',
        'CREM' => 'Event',
        'BAPM' => 'Event',
        'BARM' => 'Event',
        'BASM' => 'Event',
        'BLES' => 'Event',
        'CHRA' => 'Event',
        'CONF' => 'Event',
        'FCOM' => 'Event',
        'ORDN' => 'Event',
        'NATU' => 'Event',
        'EMIG' => 'Event',
        'IMMI' => 'Event',
        'CENS' => 'Event',
        'PROB' => 'Event',
        'WILL' => 'Event',
        'GRAD' => 'Event',
        'RETI' => 'Event',
        'ADOP' => 'Event',
        'MARR' => 'Event',
        'DIV'  => 'Event',
        'ANUL' => 'Event',
        'ENGA' => 'Event',
        'MARB' => 'Event',
        'MARC' => 'Event',
        'MARL' => 'Event',
        'MARS' => 'Event',
        'DIVF' => 'Event',
        // Attributes.
        'CAST' => 'Attribute',
        'DSCR' => 'Attribute',
        'EDUC' => 'Attribute',
        'IDNO' => 'Attribute',
        'NATI' => 'Attribute',
        'NCHI' => 'Attribute',
        'NMR'  => 'Attribute',
        'OCCU' => 'Attribute',
        'PROP' => 'Attribute',
        'RELI' => 'Attribute',
        'RESI' => 'Attribute',
        'SSN'  => 'Attribute',
        'TITL' => 'Attribute',
        'FACT' => 'Attribute',
    ];

    /**
     * Resolves the target namespace for a structure's generated class.
     *
     * @param string $tag      The structure's GEDCOM tag.
     * @param bool   $isRecord Whether the structure is a level-0 record.
     *
     * @return string The fully-qualified target namespace (without a leading backslash).
     */
    public function namespaceFor(string $tag, bool $isRecord): string
    {
        if ($isRecord) {
            return self::BASE . '\\Record';
        }

        return self::BASE . '\\Substructure\\' . (self::DOMAINS[$tag] ?? 'Common');
    }
}
