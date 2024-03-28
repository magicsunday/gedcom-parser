<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use DateTime;
use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Model\DataObject;

use function count;

/**
 * A date.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class DateExact extends DataObject implements DateExactInterface
{
    public const DATE_FORMAT = 'd M Y';

    public const TIME_FORMAT = 'H:i:s.u';

    /**
     * {@inheritDoc}
     */
    public function getDate(): ?string
    {
        return $this->getValue(self::TAG_DATE_EXACT);
    }

    /**
     * {@inheritDoc}
     */
    public function getTime(): ?string
    {
        return $this->getValue(self::TAG_TIME);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTime()
    {
        $dateTime = $this->createDateFromFormat($this->getDate());

        if ($dateTime !== false) {
            return $this->createTimeFromFormat($dateTime, $this->getTime());
        }

        return $dateTime;
    }

    /**
     * @param string $date
     *
     * @return bool|DateTime
     */
    private function createDateFromFormat(string $date)
    {
        $dateTime = DateTime::createFromFormat(self::DATE_FORMAT, $date);

        if ($dateTime !== false) {
            // Unset time
            $dateTime->setTime(0, 0);
        }

        return $dateTime;
    }

    /**
     * Add the time component.
     *
     * @param DateTime $dateTime
     * @param string   $time
     *
     * @return DateTime
     */
    private function createTimeFromFormat(DateTime $dateTime, string $time): DateTime
    {
        // Fraction part
        if (($fractionPos = strpos($time, '.')) !== false) {
            // TODO Add milliseconds part (available only in PHP7.1+)
            // $fraction = (int) substr($time, $fractionPos + 1);
            $time = substr($time, 0, $fractionPos);
        }

        $timeParts = array_map('\intval', explode(':', $time));

        // Add seconds part if missing
        if (count($timeParts) === 2) {
            $timeParts[2] = 0;
        }

        // TODO Add milliseconds part (available only in PHP7.1+)
        $dateTime->setTime(
            $timeParts[0],
            $timeParts[1],
            $timeParts[2]
        );

        return $dateTime;
    }
}
