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
 * A GEDCOM 7.0 note-text translation (the `TRAN` substructure of a shared note).
 *
 * A shared note may carry its text in additional languages or media types; each rendering is a
 * `TRAN` line carrying the translated text as its value, together with its own language and media
 * type. The text is nullable because GEDCOM 7.0 lets a structure stand on its substructures alone:
 * a `TRAN` that documents only a `LANG`/`MIME` with an empty payload is valid, so keeping the value
 * nullable stops such a translation from dropping the whole list.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class NoteTranslation
{
    /**
     * @param string|null $value The translated note text (the TRAN line value), or NULL when absent.
     * @param string|null $lang  The BCP-47 language tag of the translation (TRAN.LANG), or NULL when absent.
     * @param string|null $mime  The media type of the translated text (TRAN.MIME), or NULL when absent.
     */
    public function __construct(
        public ?string $value = null,
        public ?string $lang = null,
        public ?string $mime = null,
    ) {
    }
}
