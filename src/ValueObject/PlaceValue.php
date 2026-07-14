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
use function explode;
use function trim;

/**
 * A parsed GEDCOM PLACE_NAME value.
 *
 * A place name is a comma-separated jurisdictional hierarchy ordered from the most specific
 * entity to the least (e.g. `Cove, Cache, Utah, USA`). An optional place `FORM` names the
 * jurisdictions in the same sequence (`City, County, State, Country`). Empty positions are
 * preserved so a level always lines up with its FORM label. The original raw text is kept.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class PlaceValue
{
    /**
     * @param list<string> $levels The trimmed jurisdiction names, most specific first (empties kept)
     * @param string|null  $form   The raw place FORM, or NULL when none is declared
     * @param string       $raw    The original, unparsed PLACE_NAME value
     */
    public function __construct(
        public array $levels,
        public ?string $form,
        public string $raw,
    ) {
    }

    /**
     * Parses a raw GEDCOM PLACE_NAME (and optional FORM) into a typed value object.
     *
     * @param string      $place The raw place name, e.g. `Cove, Cache, Utah, USA`
     * @param string|null $form  The raw place FORM, e.g. `City, County, State, Country`
     */
    public static function fromGedcom(string $place, ?string $form = null): self
    {
        $trimmed = trim($place);
        $levels  = $trimmed === '' ? [] : array_map(trim(...), explode(',', $trimmed));

        if (($form !== null) && (trim($form) === '')) {
            $form = null;
        }

        return new self($levels, $form, $place);
    }

    /**
     * Maps the FORM labels onto the hierarchy levels by position.
     *
     * @return array<string, string> The FORM label mapped to its jurisdiction value; empty when
     *                               no FORM is present. Empty labels and surplus levels are skipped.
     */
    public function mapped(): array
    {
        if ($this->form === null) {
            return [];
        }

        $labels = array_map(trim(...), explode(',', $this->form));
        $result = [];

        foreach ($labels as $index => $label) {
            if ($label === '') {
                continue;
            }

            if (!isset($this->levels[$index])) {
                continue;
            }

            $result[$label] = $this->levels[$index];
        }

        return $result;
    }
}
