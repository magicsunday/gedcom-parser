<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

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
     * @param DateTime|string $date The date
     */
    public function __construct($date)
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
     * @param DateTime|string $date The date
     *
     * @return self
     */
    public function setDate($date): self
    {
        if (is_string($date)) {
            $this->dateTime = DateTime::createFromFormat(self::DATE_FORMAT, $date);
        } elseif ($date instanceof DateTime) {
            $this->dateTime = $date;
        }

        if (!$this->dateTime) {
            throw new InvalidArgumentException('Failed to parse date. Required format: d M Y');
        }

        // Unset time
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
        $tmp = DateTime::createFromFormat(self::TIME_FORMAT, $time);

        // TODO Add milliseconds part (available only in PHP7.1+)
        $this->dateTime->setTime(
            (int) $tmp->format('H'),
            (int) $tmp->format('i'),
            (int) $tmp->format('s')
        );

        return $this;
    }
}
