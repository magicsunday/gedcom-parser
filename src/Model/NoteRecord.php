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
 * media type ({@see self::$lang}, {@see self::$mime}) and any translations of the text
 * ({@see self::$tran}); those stay empty/NULL for a 5.5.1 note.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class NoteRecord
{
    /**
     * @param string                   $xref  The record cross-reference identifier.
     * @param string|null              $value The note text (the record's line value), or NULL when empty.
     * @param string|null              $lang  The GEDCOM 7.0 BCP-47 language tag (SNOTE.LANG), or NULL when absent.
     * @param string|null              $mime  The GEDCOM 7.0 media type of the text (SNOTE.MIME), or NULL when absent.
     * @param list<NoteTranslation>    $tran  The GEDCOM 7.0 translations of the note text (SNOTE.TRAN); empty when none.
     * @param list<string>             $uid   The GEDCOM 7.0 unique identifiers (UID); empty when none.
     * @param list<ExternalIdentifier> $exid  The GEDCOM 7.0 external identifiers (EXID); empty when none.
     * @param CreationDate|null        $crea  The GEDCOM 7.0 record creation timestamp (CREA), or NULL when absent.
     * @param ChangeDate|null          $chan  The record change timestamp (CHAN), or NULL when absent.
     */
    public function __construct(
        public string $xref,
        public ?string $value = null,
        public ?string $lang = null,
        public ?string $mime = null,
        public array $tran = [],
        public array $uid = [],
        public array $exid = [],
        public ?CreationDate $crea = null,
        public ?ChangeDate $chan = null,
    ) {
    }
}
