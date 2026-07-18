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
 * A typed pedigree (`PEDI`) — how a child is related to the family it is linked to.
 *
 * The {@see \MagicSunday\Gedcom\Enumeration\PedigreeType} lists the values this may carry; this is the
 * value as a document states it, qualifier and all.
 *
 * The pedigree is an enumerated value ({@see $value}, e.g. `BIRTH`, `ADOPTED`, `FOSTER`), which
 * GEDCOM 7.0 optionally qualifies by a free-text {@see $phrase} describing an `OTHER` or otherwise
 * imprecise relationship. The value is kept verbatim, so an extension or unlisted pedigree survives
 * rather than being rejected.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Pedigree
{
    /**
     * @param string|null           $value   The enumerated pedigree value (PEDI), preserved verbatim, or NULL when absent.
     * @param string|null           $phrase  The GEDCOM 7.0 free-text phrase qualifying the pedigree (PHRASE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $phrase = null,
        public array $unknown = [],
    ) {
    }
}
