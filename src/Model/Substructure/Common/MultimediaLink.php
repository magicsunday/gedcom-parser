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
 * A typed multimedia link (`OBJE`) — a reference from a record to a multimedia record.
 *
 * The linked multimedia record is referenced by its cross-reference pointer ({@see $xref}, which may
 * be the GEDCOM 7.0 `@VOID@` placeholder), and an optional descriptive {@see $titl title} overrides
 * the title carried by the multimedia record itself. The GEDCOM 5.5.1 inline multimedia form (an
 * embedded `FILE`/`FORM` block instead of a pointer) and the GEDCOM 7.0 `CROP` subregion are not yet
 * typed; they are preserved verbatim on {@see $unknown} pending a dedicated model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class MultimediaLink
{
    /**
     * @param string|null           $xref    The linked multimedia record's cross-reference pointer (or the GEDCOM 7.0 `@VOID@` placeholder), or NULL for the GEDCOM 5.5.1 inline form.
     * @param string|null           $titl    The descriptive title overriding the multimedia record's own (TITL), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (the inline FILE/FORM block, CROP, extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $xref = null,
        public ?string $titl = null,
        public array $unknown = [],
    ) {
    }
}
