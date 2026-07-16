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
 * A GEDCOM 7.0 external identifier of a record (the `EXID` substructure).
 *
 * Carries an identifier maintained by an external authority — a persistent identifier assigned
 * outside this dataset — together with the URI of that authority ({@see self::$type}). The type is
 * nullable because GEDCOM 7.0 permits (while deprecating) an `EXID` without a `TYPE`, and the value
 * is nullable because the grammar lets the structure stand on its `TYPE` substructure alone.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class ExternalIdentifier
{
    /**
     * @param string|null           $value   The external identifier (the EXID line value), or NULL when absent.
     * @param string|null           $type    The URI of the authority that issued the identifier (EXID.TYPE), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $type = null,
        public array $unknown = [],
    ) {
    }
}
