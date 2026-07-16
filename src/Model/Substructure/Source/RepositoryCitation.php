<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Substructure\Source;

use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\Substructure\Common\CallNumber;
use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A citation of a repository record, with the source call numbers held there and any notes.
 *
 * This class is generated from the GEDCOM registry. Do not edit it by hand.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class RepositoryCitation
{
    /**
     * @param string|null           $xref    The referenced record cross-reference, or NULL when the structure is not a pointer.
     * @param list<CallNumber>      $caln    The CALN substructures.
     * @param list<Note>            $note    The NOTE substructures.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-place tags), preserved verbatim.
     */
    public function __construct(
        public ?string $xref = null,
        public array $caln = [],
        public array $note = [],
        public array $unknown = [],
    ) {
    }
}
