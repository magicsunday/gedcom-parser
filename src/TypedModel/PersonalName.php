<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\TypedModel;

use function implode;
use function preg_replace;
use function str_replace;
use function strpos;
use function substr;
use function trim;

/**
 * A typed GEDCOM personal name (the `NAME` structure of an individual).
 *
 * The `NAME` structure carries both a value — the slash-delimited name string such as
 * `John /Doe/`, where the surname is delimited by slashes — and optional name-part
 * substructures. The raw value and every explicit `{0:1}` name piece are exposed as-is, while
 * {@see getGivenName()}, {@see getSurname()} and {@see getSuffix()} interpret the slash
 * convention (an explicit piece always winning over the slash-derived part) and
 * {@see getDisplayName()} yields a slash-free rendering.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class PersonalName
{
    /**
     * @param string|null $value The slash-delimited name value, or NULL when absent.
     * @param string|null $givn  The given name from the GIVN substructure, or NULL.
     * @param string|null $surn  The surname from the SURN substructure, or NULL.
     * @param string|null $npfx  The name prefix from the NPFX substructure, or NULL.
     * @param string|null $spfx  The surname prefix from the SPFX substructure, or NULL.
     * @param string|null $nsfx  The name suffix from the NSFX substructure, or NULL.
     * @param string|null $nick  The nickname from the NICK substructure, or NULL.
     * @param string|null $type  The name type from the TYPE substructure, or NULL.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $givn = null,
        public ?string $surn = null,
        public ?string $npfx = null,
        public ?string $spfx = null,
        public ?string $nsfx = null,
        public ?string $nick = null,
        public ?string $type = null,
    ) {
    }

    /**
     * Returns the given name: the explicit GIVN piece when present, otherwise the part of the
     * value before the first slash.
     *
     * @return string|null The given name, or NULL when neither is available.
     */
    public function getGivenName(): ?string
    {
        return $this->givn ?? $this->slashParts()['given'];
    }

    /**
     * Returns the surname: the explicit SURN piece when present, otherwise the part of the value
     * delimited by slashes (a missing trailing slash being tolerated).
     *
     * @return string|null The surname, or NULL when neither is available.
     */
    public function getSurname(): ?string
    {
        return $this->surn ?? $this->slashParts()['surname'];
    }

    /**
     * Returns the name suffix: the explicit NSFX piece when present, otherwise the part of the
     * value after the closing slash.
     *
     * @return string|null The suffix, or NULL when neither is available.
     */
    public function getSuffix(): ?string
    {
        return $this->nsfx ?? $this->slashParts()['suffix'];
    }

    /**
     * Returns a human-readable display name that never contains a slash: the raw value with its
     * surname-delimiting slashes stripped and its whitespace collapsed, or — when no value is
     * present — the explicit name pieces joined in reading order.
     *
     * @return string The slash-free display name (an empty string when nothing is known)
     */
    public function getDisplayName(): string
    {
        if ($this->value !== null) {
            return $this->collapseWhitespace(str_replace('/', ' ', $this->value));
        }

        $pieces = [];

        foreach ([$this->npfx, $this->givn, $this->spfx, $this->surn, $this->nsfx] as $piece) {
            if (($piece !== null) && ($piece !== '')) {
                $pieces[] = $piece;
            }
        }

        return $this->collapseWhitespace(implode(' ', $pieces));
    }

    /**
     * Derives the given name, surname and suffix from the slash-delimited value. Each part is NULL
     * when the value is absent or the derived fragment is empty.
     *
     * @return array{given: ?string, surname: ?string, suffix: ?string} The slash-derived parts.
     */
    private function slashParts(): array
    {
        $value = $this->value;

        if ($value === null) {
            return ['given' => null, 'surname' => null, 'suffix' => null];
        }

        $firstSlash = strpos($value, '/');

        if ($firstSlash === false) {
            return ['given' => $this->emptyToNull(trim($value)), 'surname' => null, 'suffix' => null];
        }

        $given     = trim(substr($value, 0, $firstSlash));
        $remainder = substr($value, $firstSlash + 1);
        $nextSlash = strpos($remainder, '/');

        if ($nextSlash === false) {
            // A missing trailing slash: everything after the opening slash is the surname.
            return [
                'given'   => $this->emptyToNull($given),
                'surname' => $this->emptyToNull(trim($remainder)),
                'suffix'  => null,
            ];
        }

        return [
            'given'   => $this->emptyToNull($given),
            'surname' => $this->emptyToNull(trim(substr($remainder, 0, $nextSlash))),
            'suffix'  => $this->emptyToNull(trim(substr($remainder, $nextSlash + 1))),
        ];
    }

    /**
     * Collapses runs of whitespace into a single space and trims the result.
     *
     * @param string $value The value to normalise.
     *
     * @return string The whitespace-collapsed value.
     */
    private function collapseWhitespace(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    /**
     * Maps an empty string to NULL, leaving every other value untouched.
     *
     * @param string $value The value to normalise.
     *
     * @return string|null The value, or NULL when it is empty.
     */
    private function emptyToNull(string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
