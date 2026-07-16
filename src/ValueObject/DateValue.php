<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\ValueObject;

use function count;
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
     * @param DateType              $type    The kind of date value.
     * @param CalendarDate|null     $date    The (start) date, or NULL for a bare phrase.
     * @param CalendarDate|null     $endDate The end date of a range or period, or NULL.
     * @param string|null           $phrase  The free-text phrase of a phrase/interpreted date, or NULL.
     * @param string                $raw     The original, unparsed DATE_VALUE.
     * @param list<RawSubstructure> $unknown Substructures nested under the DATE that its grammar does not consume (extensions and out-of-place tags), preserved verbatim.
     */
    public function __construct(
        public DateType $type,
        public ?CalendarDate $date,
        public ?CalendarDate $endDate,
        public ?string $phrase,
        public string $raw,
        public array $unknown = [],
    ) {
    }

    /**
     * Parses a raw GEDCOM DATE_VALUE, optionally carrying an explicit GEDCOM 7.0 PHRASE
     * substructure, into a typed value object.
     *
     * A value-less DATE carried solely by its PHRASE substructure becomes a {@see DateType::Phrase}
     * date whose phrase is that text; a valued DATE that also carries a PHRASE keeps its parsed form
     * and records the phrase alongside.
     *
     * @param string                $value   The raw DATE_VALUE, e.g. `ABT 1900`, `BET 1900 AND 1910` or `INT 1900 (guess)`.
     * @param string|null           $phrase  The GEDCOM 7.0 PHRASE substructure text, or NULL when none is present.
     * @param list<RawSubstructure> $unknown Substructures nested under the DATE that its grammar does not consume, preserved verbatim.
     */
    public static function fromGedcom(string $value, ?string $phrase = null, array $unknown = []): self
    {
        $explicitPhrase = $phrase !== null ? trim($phrase) : null;

        if ($explicitPhrase === '') {
            $explicitPhrase = null;
        }

        // A value-less GEDCOM 7.0 DATE may be carried solely by its PHRASE substructure, which is
        // then the date's only textual form (the empty date value is kept as the raw text).
        if (($explicitPhrase !== null) && (trim($value) === '')) {
            return new self(DateType::Phrase, null, null, $explicitPhrase, $value, $unknown);
        }

        $parsed = self::parse($value);

        if ($explicitPhrase === null) {
            return ($unknown === [])
                ? $parsed
                : new self($parsed->type, $parsed->date, $parsed->endDate, $parsed->phrase, $parsed->raw, $unknown);
        }

        // A valued DATE that also carries an explicit PHRASE keeps its parsed form and records the
        // phrase, overriding any inline phrase from the value grammar.
        return new self($parsed->type, $parsed->date, $parsed->endDate, $explicitPhrase, $parsed->raw, $unknown);
    }

    /**
     * Parses a raw GEDCOM DATE_VALUE into a typed value object.
     *
     * @param string $value The raw DATE_VALUE, e.g. `ABT 1900`, `BET 1900 AND 1910` or `INT 1900 (guess)`.
     */
    private static function parse(string $value): self
    {
        $trimmed = trim($value);

        // A bare free-text phrase: (text).
        if (preg_match('/^\((.*)\)$/s', $trimmed, $matches) === 1) {
            return new self(DateType::Phrase, null, null, $matches[1], $value);
        }

        // An interpreted date: INT <date> (phrase). The date may be omitted in malformed input.
        if (preg_match('/^INT\s+(.*?)\s*\((.*)\)$/is', $trimmed, $matches) === 1) {
            $date = trim($matches[1]) === '' ? null : CalendarDate::fromGedcom($matches[1]);

            return new self(DateType::Interpreted, $date, null, $matches[2], $value);
        }

        if (preg_match('/^(ABT|CAL|EST|BEF|AFT|BET|FROM|TO)\b\s*(.*)$/is', $trimmed, $matches) === 1) {
            return self::fromKeyword($matches[1], $matches[2], $value);
        }

        // A plain date. GEDCOM technically treats a day+month-only value (`2 JAN`) as a date
        // phrase, but keeping it an Exact date whose CalendarDate carries a null year preserves
        // the parsed day/month rather than discarding them; a consumer detects the partial form
        // via the null year.
        return new self(DateType::Exact, CalendarDate::fromGedcom($trimmed), null, null, $value);
    }

    /**
     * Builds the value object for a recognised leading keyword.
     *
     * @param string $keyword The matched keyword (any case)
     * @param string $rest    The remainder after the keyword.
     * @param string $raw     The original, unparsed DATE_VALUE.
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
     * @param string $rest The remainder after `FROM`.
     * @param string $raw  The original, unparsed DATE_VALUE.
     */
    private static function fromPeriod(string $rest, string $raw): self
    {
        if (preg_match('/\s+TO\b/i', $rest) === 1) {
            return self::fromTwoDates(DateType::FromTo, $rest, 'TO', $raw);
        }

        return new self(DateType::From, CalendarDate::fromGedcom($rest), null, null, $raw);
    }

    /**
     * Splits a two-date range/period on its separator keyword and builds the value object.
     *
     * @param DateType $type      The resulting date type.
     * @param string   $rest      The two dates joined by the separator.
     * @param string   $separator The uppercase separator keyword (`AND` or `TO`)
     * @param string   $raw       The original, unparsed DATE_VALUE.
     */
    private static function fromTwoDates(DateType $type, string $rest, string $separator, string $raw): self
    {
        $parts = preg_split('/\s+' . $separator . '\b\s*/i', $rest);

        if ($parts === false) {
            $parts = [$rest];
        }

        // The grammar allows the separator exactly once; a repeated separator or a missing/empty
        // second date leaves the range/period open-ended rather than binding a wrong second date.
        $end = (count($parts) === 2) && ($parts[1] !== '')
            ? CalendarDate::fromGedcom($parts[1])
            : null;

        return new self($type, CalendarDate::fromGedcom($parts[0] ?? $rest), $end, null, $raw);
    }
}
