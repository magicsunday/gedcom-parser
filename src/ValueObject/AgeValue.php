<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\ValueObject;

use function preg_match;
use function strtoupper;
use function substr;
use function trim;

/**
 * A parsed GEDCOM AGE_AT_EVENT value.
 *
 * The 5.5.1 grammar is an optional relational qualifier (`<` / `>`) followed by either a
 * symbolic keyword (`CHILD` / `INFANT` / `STILLBORN`) or any combination of a years / months /
 * days duration (`72y 3m 2d`). The original raw text is preserved alongside the parsed parts.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class AgeValue
{
    /**
     * @param AgeModifier|null $modifier The relational qualifier, or NULL when the age is exact
     * @param AgeKeyword|null  $keyword  The symbolic keyword, or NULL when a duration is given
     * @param int|null         $years    The number of full years, or NULL when absent
     * @param int|null         $months   The number of months, or NULL when absent
     * @param int|null         $days     The number of days, or NULL when absent
     * @param string           $raw      The original, unparsed AGE value
     */
    public function __construct(
        public ?AgeModifier $modifier,
        public ?AgeKeyword $keyword,
        public ?int $years,
        public ?int $months,
        public ?int $days,
        public string $raw,
    ) {
    }

    /**
     * Parses a raw GEDCOM AGE_AT_EVENT value into a typed value object.
     *
     * @param string $raw The raw AGE value, e.g. `72y 3m 2d`, `< 8y` or `CHILD`
     */
    public static function fromGedcom(string $raw): self
    {
        $value = trim($raw);

        $modifier = null;

        if ($value !== '') {
            $modifier = AgeModifier::tryFrom($value[0]);

            if ($modifier instanceof AgeModifier) {
                $value = trim(substr($value, 1));
            }
        }

        $keyword = AgeKeyword::tryFrom(strtoupper($value));

        $years  = null;
        $months = null;
        $days   = null;

        // The whole value must match the ordered YYy MMm DDDd grammar (any subset). A number and
        // its label are concatenated; pairs are whitespace-separated. Anchoring rejects reordered
        // or garbage input outright rather than salvaging wrong fields from it.
        if (
            !$keyword instanceof AgeKeyword
            && (preg_match('/^(?:(\d+)y)?(?:\s*(\d+)m)?(?:\s*(\d+)d)?$/i', $value, $matches) === 1)
        ) {
            $years  = self::toInt($matches[1] ?? '');
            $months = self::toInt($matches[2] ?? '');
            $days   = self::toInt($matches[3] ?? '');
        }

        return new self($modifier, $keyword, $years, $months, $days, $raw);
    }

    /**
     * Converts a captured duration group into an integer, or NULL when the group was absent.
     *
     * @param string $match The captured digits, or an empty string when the label was not present
     *
     * @return int|null The parsed count, or NULL
     */
    private static function toInt(string $match): ?int
    {
        return $match !== '' ? (int) $match : null;
    }
}
