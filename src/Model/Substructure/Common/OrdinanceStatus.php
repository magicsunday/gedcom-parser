<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Common;

use MagicSunday\Gedcom\Model\ExactDate;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed GEDCOM 7.0 ordinance status (`STAT`) — the completion status of an LDS ordinance.
 *
 * The {@see $value} is an enumerated status (such as `COMPLETED`, `EXCLUDED` or `SUBMITTED`, kept
 * verbatim so an extension survives), and {@see $date} is the exact date — with its optional
 * wall-clock time — on which that status was assigned. The status date is an exact date (not a
 * genealogical date value), so it is modelled as an {@see ExactDate} to preserve its `TIME` child.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class OrdinanceStatus
{
    /**
     * @param string|null           $value   The enumerated status value (STAT), preserved verbatim, or NULL when absent.
     * @param ExactDate|null        $date    The exact date (and optional time) the status was assigned (DATE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?ExactDate $date = null,
        public array $unknown = [],
    ) {
    }
}
