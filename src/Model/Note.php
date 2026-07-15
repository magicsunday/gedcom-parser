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
 * An inline GEDCOM note (the `NOTE` substructure carried inside another structure).
 *
 * Unlike a shared {@see NoteRecord}, an inline note carries its text directly on the structure that
 * owns it rather than by cross-reference. GEDCOM 7.0 additionally documents the note's language and
 * media type ({@see self::$lang}, {@see self::$mime}) and any translations of the text
 * ({@see self::$tran}); those stay empty/NULL for a 5.5.1 note, whose text (or a pointer to a shared
 * note) is carried as the plain {@see self::$value}.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Note
{
    /**
     * @param string|null           $value The note text, or a pointer to a shared note in 5.5.1, or NULL when absent.
     * @param string|null           $lang  The GEDCOM 7.0 BCP-47 language tag (NOTE.LANG), or NULL when absent.
     * @param string|null           $mime  The GEDCOM 7.0 media type of the text (NOTE.MIME), or NULL when absent.
     * @param list<NoteTranslation> $tran  The GEDCOM 7.0 translations of the note text (NOTE.TRAN); empty when none.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $lang = null,
        public ?string $mime = null,
        public array $tran = [],
    ) {
    }
}
