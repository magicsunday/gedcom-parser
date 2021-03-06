<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\File;

/**
 * The OBJE (object), FILE, FORM (format) structure tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface FormatInterface
{
    /**
     * Indicates the format of the multimedia data associated with the specific GEDCOM context. This
     * allows processors to determine whether they can process the data object. Any linked files should
     * contain the data required, in the indicated format, to process the file data.
     *
     * - bmp
     * - gif
     * - jpg
     * - ole
     * - pcx
     * - tif
     * - wav
     */
    const TAG_TYPE = 'MULTIMEDIA_FORMAT';

    /**
     * A code, selected from one of the media classifications choices above, that indicates the type of
     * material in which the referenced source is stored.
     *
     * - audio
     * - book
     * - card
     * - electronic
     * - fiche
     * - film
     * - magazine
     * - manuscript
     * - map
     * - newspaper
     * - photo
     * - tombstone
     * - video
     */
    const TAG_MEDI = 'MEDI';

    /**
     * @return null|string
     */
    public function getMediaFormat();

    /**
     * @return null|string
     */
    public function getMediaType();
}
