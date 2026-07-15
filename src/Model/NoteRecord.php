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
 * A typed GEDCOM shared note (5.5.1 `NOTE`, 7.0 `SNOTE`) record.
 *
 * A note record carries its text as the record's own line value — reassembled across any
 * `CONC`/`CONT` continuation lines — so that other records may reference it by cross-reference
 * rather than repeating the text inline. GEDCOM 7.0 additionally documents the note's language and
 * media type; those are exposed as {@see self::$lang} and {@see self::$mime} and stay NULL for a
 * 5.5.1 note.
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
     * @param string|null $lang  The GEDCOM 7.0 BCP-47 language tag (SNOTE.LANG), or NULL when absent.
     * @param string|null $mime  The GEDCOM 7.0 media type of the text (SNOTE.MIME), or NULL when absent.
     */
    public function __construct(
        public string $xref,
        public ?string $value = null,
        public ?string $lang = null,
        public ?string $mime = null,
    ) {
    }
}
