<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Common;

use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A repository call number: the identifier a repository files an item under, and its media type.
 *
 * This class is generated from the GEDCOM registry. Do not edit it by hand.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class CallNumber
{
    /**
     * @param string|null           $value   The structure's line value.
     * @param string|null           $medi    The MEDI value.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-place tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $medi = null,
        public array $unknown = [],
    ) {
    }
}
