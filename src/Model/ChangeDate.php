<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * The change timestamp of a record (the `CHAN` substructure).
 *
 * Records when the record was last changed, as an exact date with an optional time
 * ({@see self::$date}), together with any notes documenting the change — inline notes
 * ({@see self::$note}) and, in GEDCOM 7.0, references to shared notes ({@see self::$snote}). Unlike
 * the 7.0-only creation timestamp, the change timestamp exists in both GEDCOM versions, so this
 * value object is populated for a 5.5.1 record as well.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class ChangeDate
{
    /**
     * @param ExactDate|null        $date    The exact date (and optional time) the record was changed (CHAN.DATE), or NULL.
     * @param list<Note>            $note    The inline notes documenting the change (CHAN.NOTE); empty when none.
     * @param list<string>          $snote   The GEDCOM 7.0 shared-note references documenting the change (CHAN.SNOTE); empty when none.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?ExactDate $date = null,
        public array $note = [],
        public array $snote = [],
        public array $unknown = [],
    ) {
    }
}
