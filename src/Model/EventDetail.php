<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Substructure\Source\SourceCitation;
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * The typed detail shared by GEDCOM events: when and where the event took place, and the
 * individual's age at it.
 *
 * Each is exposed as its typed value object — {@see DateValue}, {@see PlaceValue},
 * {@see AgeValue} — parsed from the event's `DATE` / `PLAC` / `AGE` substructures by the mapping
 * layer's custom type handlers.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class EventDetail
{
    /**
     * @param DateValue|null        $date    The date the event took place, or NULL when absent.
     * @param PlaceValue|null       $plac    The place the event took place, or NULL when absent.
     * @param AgeValue|null         $age     The individual's age at the event, or NULL when absent.
     * @param list<SourceCitation>  $sour    The source citations supporting the event.
     * @param string|null           $type    The user-supplied classification of the event (TYPE), or NULL when absent.
     * @param string|null           $caus    The cause of the event (CAUS), or NULL when absent.
     * @param string|null           $resn    The restriction notice (RESN), preserved verbatim, or NULL when absent.
     * @param list<Note>            $note    The notes on the event (NOTE).
     * @param list<string>          $phon    The phone numbers (PHON); empty when none.
     * @param list<string>          $email   The email addresses (EMAIL); empty when none.
     * @param list<string>          $fax     The fax numbers (FAX); empty when none.
     * @param list<string>          $www     The web pages (WWW); empty when none.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?DateValue $date = null,
        public ?PlaceValue $plac = null,
        public ?AgeValue $age = null,
        public array $sour = [],
        public ?string $type = null,
        public ?string $caus = null,
        public ?string $resn = null,
        public array $note = [],
        public array $phon = [],
        public array $email = [],
        public array $fax = [],
        public array $www = [],
        public array $unknown = [],
    ) {
    }
}
