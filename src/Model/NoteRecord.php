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
 * A typed GEDCOM shared note (NOTE) record.
 *
 * A note record carries its text as the record's own line value — reassembled across any
 * `CONC`/`CONT` continuation lines — so that other records may reference it by cross-reference
 * rather than repeating the text inline.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class NoteRecord
{
    /**
     * @param string      $xref  The record cross-reference identifier.
     * @param string|null $value The note text (the record's line value), or NULL when empty.
     */
    public function __construct(
        public string $xref,
        public ?string $value = null,
    ) {
    }
}
