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
use function preg_split;
use function strtoupper;
use function trim;

/**
 * A parsed GEDCOM DATE_VALUE.
 *
 * Wraps the qualifier grammar around one or two {@see CalendarDate}s: an exact date; an
 * approximation (`ABT` / `CAL` / `EST`); a range (`BEF` / `AFT` / `BET … AND …`); a period
 * (`FROM` / `TO` / `FROM … TO …`); an interpreted date (`INT … (phrase)`); or a bare free-text
 * phrase (`(…)`). The original raw text is preserved.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class DateValue
{
    /**
     * @param DateType          $type    The kind of date value
     * @param CalendarDate|null $date    The (start) date, or NULL for a bare phrase
     * @param CalendarDate|null $endDate The end date of a range or period, or NULL
     * @param string|null       $phrase  The free-text phrase of a phrase/interpreted date, or NULL
     * @param string            $raw     The original, unparsed DATE_VALUE
     */
    public function __construct(
        public DateType $type,
        public ?CalendarDate $date,
        public ?CalendarDate $endDate,
        public ?string $phrase,
        public string $raw,
    ) {
    }

    /**
     * Parses a raw GEDCOM DATE_VALUE into a typed value object.
     *
     * @param string $value The raw DATE_VALUE, e.g. `ABT 1900`, `BET 1900 AND 1910` or `INT 1900 (guess)`
     */
    public static function fromGedcom(string $value): self
    {
        $trimmed = trim($value);

        // A bare free-text phrase: (text).
        if (preg_match('/^\((.*)\)$/s', $trimmed, $matches) === 1) {
            return new self(DateType::Phrase, null, null, $matches[1], $value);
        }

        // An interpreted date: INT <date> (phrase).
        if (preg_match('/^INT\s+(.*?)\s*\((.*)\)$/is', $trimmed, $matches) === 1) {
            return new self(DateType::Interpreted, CalendarDate::fromGedcom($matches[1]), null, $matches[2], $value);
        }

        if (preg_match('/^(ABT|CAL|EST|BEF|AFT|BET|FROM|TO)\b\s*(.*)$/is', $trimmed, $matches) === 1) {
            return self::fromKeyword($matches[1], $matches[2], $value);
        }

        return new self(DateType::Exact, CalendarDate::fromGedcom($trimmed), null, null, $value);
    }

    /**
     * Builds the value object for a recognised leading keyword.
     *
     * @param string $keyword The matched keyword (any case)
     * @param string $rest    The remainder after the keyword
     * @param string $raw     The original, unparsed DATE_VALUE
     */
    private static function fromKeyword(string $keyword, string $rest, string $raw): self
    {
        return match (strtoupper($keyword)) {
            'ABT'   => new self(DateType::About, CalendarDate::fromGedcom($rest), null, null, $raw),
            'CAL'   => new self(DateType::Calculated, CalendarDate::fromGedcom($rest), null, null, $raw),
            'EST'   => new self(DateType::Estimated, CalendarDate::fromGedcom($rest), null, null, $raw),
            'BEF'   => new self(DateType::Before, CalendarDate::fromGedcom($rest), null, null, $raw),
            'AFT'   => new self(DateType::After, CalendarDate::fromGedcom($rest), null, null, $raw),
            'TO'    => new self(DateType::To, CalendarDate::fromGedcom($rest), null, null, $raw),
            'BET'   => self::fromTwoDates(DateType::Between, $rest, 'AND', $raw),
            default => self::fromPeriod($rest, $raw),
        };
    }

    /**
     * Builds a FROM period, which may or may not carry a `TO` bound.
     *
     * @param string $rest The remainder after `FROM`
     * @param string $raw  The original, unparsed DATE_VALUE
     */
    private static function fromPeriod(string $rest, string $raw): self
    {
        if (preg_match('/\sTO\s/i', $rest) === 1) {
            return self::fromTwoDates(DateType::FromTo, $rest, 'TO', $raw);
        }

        return new self(DateType::From, CalendarDate::fromGedcom($rest), null, null, $raw);
    }

    /**
     * Splits a two-date range/period on its separator keyword and builds the value object.
     *
     * @param DateType $type      The resulting date type
     * @param string   $rest      The two dates joined by the separator
     * @param string   $separator The uppercase separator keyword (`AND` or `TO`)
     * @param string   $raw       The original, unparsed DATE_VALUE
     */
    private static function fromTwoDates(DateType $type, string $rest, string $separator, string $raw): self
    {
        $parts = preg_split('/\s' . $separator . '\s/i', $rest, 2);

        if ($parts === false) {
            $parts = [$rest];
        }

        $end = ($parts[1] ?? '') !== '' ? CalendarDate::fromGedcom($parts[1]) : null;

        return new self($type, CalendarDate::fromGedcom($parts[0]), $end, null, $raw);
    }
}
