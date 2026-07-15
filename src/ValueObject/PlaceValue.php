<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\ValueObject;

use function array_map;
use function count;
use function explode;
use function trim;

/**
 * A parsed GEDCOM PLACE_NAME value.
 *
 * A place name is a comma-separated jurisdictional hierarchy ordered from the most specific
 * entity to the least (e.g. `Cove, Cache, Utah, USA`). An optional place `FORM` names the
 * jurisdictions in the same sequence (`City, County, State, Country`). Empty positions are
 * preserved so a level always lines up with its FORM label. The optional `MAP` substructure's
 * geographic {@see MapCoordinates} are carried alongside. The original raw text is kept.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class PlaceValue
{
    /**
     * @param list<string>        $levels      The trimmed jurisdiction names, most specific first
     *                                         (empties kept)
     * @param string|null         $form        The raw place FORM, or NULL when none is declared
     * @param string              $raw         The original, unparsed PLACE_NAME value
     * @param MapCoordinates|null $coordinates The geographic coordinates from the MAP substructure,
     *                                         or NULL when absent or malformed
     */
    public function __construct(
        public array $levels,
        public ?string $form,
        public string $raw,
        public ?MapCoordinates $coordinates = null,
    ) {
    }

    /**
     * Parses a raw GEDCOM PLACE_NAME (and optional FORM and coordinates) into a typed value object.
     *
     * @param string              $place       The raw place name, e.g. `Cove, Cache, Utah, USA`
     * @param string|null         $form        The raw place FORM, e.g. `City, County, State, Country`
     * @param MapCoordinates|null $coordinates The geographic coordinates from the MAP substructure
     */
    public static function fromGedcom(string $place, ?string $form = null, ?MapCoordinates $coordinates = null): self
    {
        $trimmed = trim($place);
        $levels  = $trimmed === '' ? [] : array_map(trim(...), explode(',', $trimmed));

        if ($form !== null) {
            $form = trim($form);

            if ($form === '') {
                $form = null;
            }
        }

        return new self($levels, $form, $place, $coordinates);
    }

    /**
     * Maps the FORM labels onto the hierarchy levels by position.
     *
     * A positional map is only trustworthy when the FORM lines up with the hierarchy one-to-one
     * (the spec pads both with empty commas) and its labels are unambiguous. On a count mismatch
     * or a repeated label the labels would bind to the wrong jurisdiction, so no map is produced.
     * Note: only the place-local FORM is consulted; a FORM declared once in the header
     * (`HEAD.PLAC.FORM`) is not threaded here — see GH-20.
     *
     * @return array<string, string> The FORM label mapped to its jurisdiction value; empty when no
     *                               FORM is present, the counts differ, or a label repeats
     */
    public function mapped(): array
    {
        if ($this->form === null) {
            return [];
        }

        $labels = array_map(trim(...), explode(',', $this->form));

        if (count($labels) !== count($this->levels)) {
            return [];
        }

        $result = [];

        foreach ($labels as $index => $label) {
            if ($label === '') {
                continue;
            }

            if (isset($result[$label])) {
                return [];
            }

            $result[$label] = $this->levels[$index];
        }

        return $result;
    }
}
