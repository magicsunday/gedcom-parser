<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\PlaceStructure;

/**
 * The place MAP tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface MapInterface
{
    /**
     * The value specifying the latitudinal coordinate of the place name.
     */
    const TAG_LATI = 'LATI';

    /**
     * The value specifying the longitudinal coordinate of the place name.
     */
    const TAG_LONG = 'LONG';

    /**
     * @return string
     */
    public function getLatitude(): string;

    /**
     * @return string
     */
    public function getLongitude(): string;
}
