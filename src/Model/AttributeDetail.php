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
 * The typed detail shared by GEDCOM individual attributes (occupation, residence, education, …):
 * the attribute's own value and classifying `TYPE`, plus the same when/where/age event detail an
 * attribute carries.
 *
 * It extends the {@see EventDetail} shape with the attribute's line value (an `OCCU`'s occupation,
 * an `NCHI`'s count) and its `TYPE` substructure; the date, place and age are exposed as their typed
 * value objects, parsed from the attribute's `DATE` / `PLAC` / `AGE` substructures. A value-less
 * attribute (a 5.5.1 `RESI`) simply carries a NULL value.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class AttributeDetail
{
    /**
     * @param string|null           $value   The attribute's line value (an occupation, a count, …), or NULL when the attribute carries none.
     * @param string|null           $type    The user-supplied classification of the attribute (TYPE), or NULL when absent.
     * @param DateValue|null        $date    The date the attribute applies, or NULL when absent.
     * @param PlaceValue|null       $plac    The place the attribute applies, or NULL when absent.
     * @param AgeValue|null         $age     The individual's age for the attribute, or NULL when absent.
     * @param list<SourceCitation>  $sour    The source citations supporting the attribute.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $type = null,
        public ?DateValue $date = null,
        public ?PlaceValue $plac = null,
        public ?AgeValue $age = null,
        public array $sour = [],
        public array $unknown = [],
    ) {
    }
}
