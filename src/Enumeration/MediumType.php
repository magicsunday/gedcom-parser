<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Enumeration;

/**
 * The known standard values of the GEDCOM 7.0 `MEDI` enumeration set — the medium classifying a
 * multimedia file.
 *
 * These constants are typed comparison targets for the raw medium value a multimedia file's format
 * carries. That value stays a tolerant string, so an extension tag or an unlisted value is preserved
 * rather than rejected; the catch-all `OTHER` ({@see self::OTHER}) names a medium not otherwise
 * listed.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class MediumType
{
    /**
     * A sound recording.
     */
    public const string AUDIO = 'AUDIO';

    /**
     * A bound book.
     */
    public const string BOOK = 'BOOK';

    /**
     * A card or file-drawer entry.
     */
    public const string CARD = 'CARD';

    /**
     * A digital or electronic resource.
     */
    public const string ELECTRONIC = 'ELECTRONIC';

    /**
     * Microfiche.
     */
    public const string FICHE = 'FICHE';

    /**
     * Microfilm.
     */
    public const string FILM = 'FILM';

    /**
     * A magazine.
     */
    public const string MAGAZINE = 'MAGAZINE';

    /**
     * A handwritten or typescript manuscript.
     */
    public const string MANUSCRIPT = 'MANUSCRIPT';

    /**
     * A map or diagram.
     */
    public const string MAP = 'MAP';

    /**
     * A newspaper.
     */
    public const string NEWSPAPER = 'NEWSPAPER';

    /**
     * A medium not otherwise listed.
     */
    public const string OTHER = 'OTHER';

    /**
     * A photograph.
     */
    public const string PHOTO = 'PHOTO';

    /**
     * A tombstone or other grave marker.
     */
    public const string TOMBSTONE = 'TOMBSTONE';

    /**
     * A video recording.
     */
    public const string VIDEO = 'VIDEO';

    /**
     * Private constructor; this is a constant holder, not an instantiable type.
     */
    private function __construct()
    {
    }

    /**
     * Returns the known standard values of the `MEDI` enumeration set.
     *
     * @return list<string> The known standard medium values.
     */
    public static function values(): array
    {
        return [
            self::AUDIO,
            self::BOOK,
            self::CARD,
            self::ELECTRONIC,
            self::FICHE,
            self::FILM,
            self::MAGAZINE,
            self::MANUSCRIPT,
            self::MAP,
            self::NEWSPAPER,
            self::OTHER,
            self::PHOTO,
            self::TOMBSTONE,
            self::VIDEO,
        ];
    }
}
