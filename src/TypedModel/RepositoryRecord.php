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
 * A typed GEDCOM repository (REPO) record.
 *
 * Describes an archive or library that holds sources — its name (required by GEDCOM 5.5.1 but
 * tolerated as absent, so a bare record still maps) and its contact details. GEDCOM 5.5.1 permits
 * up to three of each contact number ({0:3}), so the phone, email and fax entries are lists.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class RepositoryRecord
{
    /**
     * @param string       $xref  The record cross-reference identifier.
     * @param string|null  $name  The repository name (REPO-NAME), or NULL when the record carries none.
     * @param list<string> $phon  The repository phone numbers.
     * @param list<string> $email The repository email addresses.
     * @param list<string> $fax   The repository fax numbers.
     */
    public function __construct(
        public string $xref,
        public ?string $name = null,
        public array $phon = [],
        public array $email = [],
        public array $fax = [],
    ) {
    }
}
