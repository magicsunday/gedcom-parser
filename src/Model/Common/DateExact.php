<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use DateTime;
use InvalidArgumentException;

/**
 * A date.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class DateExact
{
    const DATE_FORMAT = 'd M Y';
    const TIME_FORMAT = 'H:i:s.u';

    /**
     * The date/time.
     *
     * @var DateTime
     */
    private $dateTime;

    /**
     * Date constructor.
     *
     * @param string $date The date
     */
    public function __construct(string $date)
    {
        $this->setDate($date);
    }

    /**
     * Returns the formatted date.
     *
     * @return string
     */
    public function getDate(): string
    {
        return $this->dateTime->format(self::DATE_FORMAT);
    }

    /**
     * Sets the date.
     *
     * @param string $date The date
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function setDate(string $date): self
    {
        $dateTime = DateTime::createFromFormat(self::DATE_FORMAT, $date);

        if ($dateTime === false) {
            throw new InvalidArgumentException('Failed to parse date. Required format: d M Y');
        }

        // Unset time
        $this->dateTime = $dateTime;
        $this->dateTime->setTime(0, 0);

        return $this;
    }

    /**
     * Returns the formatted time.
     *
     * @return string
     */
    public function getTime(): string
    {
        return $this->dateTime->format(self::TIME_FORMAT);
    }

    /**
     * Sets the time.
     *
     * @param string $time The time
     *
     * @return self
     */
    public function setTime(string $time): self
    {
        // Fraction part
        if (($fracionPos = strpos($time, '.')) !== false) {
            // TODO Add milliseconds part (available only in PHP7.1+)
            //$fraction = (int) substr($time, $fracionPos + 1);
            $time     = substr($time, 0, $fracionPos);
        }

        $timeParts = array_map('\intval', explode(':', $time));

        // Add seconds part if missing
        if (\count($timeParts) === 2) {
            $timeParts[2] = 0;
        }

        // TODO Add milliseconds part (available only in PHP7.1+)
        $this->dateTime->setTime(
            $timeParts[0],
            $timeParts[1],
            $timeParts[2]
        );

        return $this;
    }
}
