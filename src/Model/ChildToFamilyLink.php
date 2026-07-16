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
 * A typed child-to-family link (the individual's `FAMC` structure).
 *
 * Links an individual to a family in which they are a child, by the family's cross-reference
 * pointer, and carries the optional pedigree (`PEDI`) that qualifies the linkage — for example
 * `birth`, `adopted` or `foster`.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class ChildToFamilyLink
{
    /**
     * @param string                $xref    The linked family's cross-reference pointer.
     * @param string|null           $pedi    The pedigree qualifying the linkage (PEDI), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public string $xref,
        public ?string $pedi = null,
        public array $unknown = [],
    ) {
    }
}
