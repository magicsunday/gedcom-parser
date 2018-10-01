<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

use DateTime;

/**
 * The change date tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface DateExactInterface
{
    /**
     * The date component.
     */
    const TAG_DATE_EXACT = 'DATE_EXACT';

    /**
     * The time component.
     */
    const TAG_TIME = 'TIME';

    /**
     * @return null|string
     */
    public function getDate();

    /**
     * @return null|string
     */
    public function getTime();

    /**
     * Get the date and time as DateTime object or false if it could not converted.
     *
     * @return bool|DateTime
     */
    public function getDateTime();
}
