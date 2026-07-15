<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\TypedModel;

/**
 * A typed spouse-to-family link (the individual's `FAMS` structure).
 *
 * Links an individual to a family in which they are a partner, by the family's cross-reference
 * pointer. Unlike a child link it carries no pedigree — a spouse linkage has no such qualifier.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SpouseToFamilyLink
{
    /**
     * @param string $xref The linked family's cross-reference pointer.
     */
    public function __construct(
        public string $xref,
    ) {
    }
}
