<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

/**
 * A typed GEDCOM submitter (SUBM) record.
 *
 * This is the first record of the schema-driven typed model: an immutable value object whose
 * properties are the submitter's cross-reference identifier and its substructures, built by the
 * mapping layer from a parsed tree via the registry schema.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class SubmitterRecord
{
    /**
     * @param string                   $xref The record cross-reference identifier.
     * @param string|null              $name The submitter's name, or NULL when the record carries none.
     * @param list<string>             $phon The submitter's phone numbers.
     * @param list<string>             $uid  The GEDCOM 7.0 unique identifiers (UID); empty when none.
     * @param list<ExternalIdentifier> $exid The GEDCOM 7.0 external identifiers (EXID); empty when none.
     */
    public function __construct(
        public string $xref,
        public ?string $name = null,
        public array $phon = [],
        public array $uid = [],
        public array $exid = [],
    ) {
    }
}
