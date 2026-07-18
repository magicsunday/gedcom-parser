<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Substructure\Common\Address;
use MagicSunday\Gedcom\Model\Substructure\Common\Association;
use MagicSunday\Gedcom\Model\Substructure\Common\EventFamilyChild;
use MagicSunday\Gedcom\Model\Substructure\Common\MultimediaLink;
use MagicSunday\Gedcom\Model\Substructure\Common\SpouseAge;
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
     * @param string|null           $value   The event's line value — a generic event's descriptor (EVEN) or an event's `Y` occurrence assertion, or NULL when absent.
     * @param DateValue|null        $date    The date the event took place, or NULL when absent.
     * @param PlaceValue|null       $plac    The place the event took place, or NULL when absent.
     * @param AgeValue|null         $age     The individual's age at the event, or NULL when absent.
     * @param list<SourceCitation>  $sour    The source citations supporting the event.
     * @param string|null           $type    The user-supplied classification of the event (TYPE), or NULL when absent.
     * @param string|null           $caus    The cause of the event (CAUS), or NULL when absent.
     * @param string|null           $resn    The restriction notice (RESN), preserved verbatim, or NULL when absent.
     * @param string|null           $agnc    The responsible agency (AGNC), or NULL when absent.
     * @param string|null           $reli    The religious affiliation (RELI), or NULL when absent.
     * @param list<Note>            $note    The notes on the event (NOTE).
     * @param list<string>          $phon    The phone numbers (PHON); empty when none.
     * @param list<string>          $email   The email addresses (EMAIL); empty when none.
     * @param list<string>          $fax     The fax numbers (FAX); empty when none.
     * @param list<string>          $www     The web pages (WWW); empty when none.
     * @param list<Association>     $asso    The GEDCOM 7.0 associations tied to the event (ASSO).
     * @param DateValue|null        $sdate   The GEDCOM 7.0 sort date (SDATE), or NULL when absent.
     * @param list<string>          $snote   The GEDCOM 7.0 shared-note cross-reference pointers (SNOTE); empty when none.
     * @param list<string>          $uid     The GEDCOM 7.0 unique identifiers (UID); empty when none.
     * @param list<MultimediaLink>  $obje    The multimedia links tied to the event (OBJE).
     * @param Address|null          $addr    The postal address the event took place at (ADDR), or NULL when absent.
     * @param EventFamilyChild|null $famc    The family the child belongs to (FAMC) on a birth, christening or adoption event, or NULL when absent.
     * @param SpouseAge|null        $husb    The husband's age at a family event (HUSB), or NULL when absent.
     * @param SpouseAge|null        $wife    The wife's age at a family event (WIFE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?DateValue $date = null,
        public ?PlaceValue $plac = null,
        public ?AgeValue $age = null,
        public array $sour = [],
        public ?string $type = null,
        public ?string $caus = null,
        public ?string $resn = null,
        public ?string $agnc = null,
        public ?string $reli = null,
        public array $note = [],
        public array $phon = [],
        public array $email = [],
        public array $fax = [],
        public array $www = [],
        public array $asso = [],
        public ?DateValue $sdate = null,
        public array $snote = [],
        public array $uid = [],
        public array $obje = [],
        public ?Address $addr = null,
        public ?EventFamilyChild $famc = null,
        public ?SpouseAge $husb = null,
        public ?SpouseAge $wife = null,
        public array $unknown = [],
    ) {
    }
}
