<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_DATE_EXACT = 'DATE_EXACT';

    /**
     * The time component.
     */
    public const TAG_TIME = 'TIME';

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @return string|null
     */
    public function getTime(): ?string;

    /**
     * Get the date and time as DateTime object or false if it could not converted.
     *
     * @return bool|DateTime
     */
    public function getDateTime();
}
