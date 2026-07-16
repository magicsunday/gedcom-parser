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
 * The GEDCOM 7.0 creation timestamp of a record (the `CREA` substructure).
 *
 * Records the moment the record was created, as an exact date with an optional time
 * ({@see self::$date}). The date is nullable because the parser tolerates a malformed `CREA` that
 * omits its otherwise required `DATE` rather than dropping the whole structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class CreationDate
{
    /**
     * @param ExactDate|null        $date    The exact date (and optional time) the record was created (CREA.DATE), or NULL.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?ExactDate $date = null,
        public array $unknown = [],
    ) {
    }
}
