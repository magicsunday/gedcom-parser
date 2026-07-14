<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Test\Model\Common;

use DateTime;
use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Model\Common\DateExact;
use MagicSunday\Gedcom\Model\DataObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the DATE_EXACT value object's date/time assembly.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
#[CoversClass(DateExact::class)]
#[UsesClass(DataObject::class)]
class DateExactTest extends TestCase
{
    /**
     * A DATE_EXACT with no date value at all yields false rather than throwing.
     */
    #[Test]
    public function getDateTimeReturnsFalseWhenNoDateIsSet(): void
    {
        self::assertFalse((new DateExact())->getDateTime());
    }

    /**
     * An unparseable date value yields false.
     */
    #[Test]
    public function getDateTimeReturnsFalseWhenTheDateIsUnparseable(): void
    {
        $date = new DateExact();
        $date->setValue(DateExactInterface::TAG_DATE_EXACT, 'not a date');

        self::assertFalse($date->getDateTime());
    }

    /**
     * A DATE_EXACT with a date but no TIME sub-value yields the date at midnight,
     * rather than throwing on the absent time.
     */
    #[Test]
    public function getDateTimeReturnsMidnightWhenTheTimeIsAbsent(): void
    {
        $date = new DateExact();
        $date->setValue(DateExactInterface::TAG_DATE_EXACT, '01 JAN 2020');

        $dateTime = $date->getDateTime();

        self::assertInstanceOf(DateTime::class, $dateTime);
        self::assertSame('2020-01-01 00:00:00', $dateTime->format('Y-m-d H:i:s'));
    }

    /**
     * A DATE_EXACT carrying both a date and a TIME applies the time to the date.
     */
    #[Test]
    public function getDateTimeAppliesTheTimeWhenPresent(): void
    {
        $date = new DateExact();
        $date->setValue(DateExactInterface::TAG_DATE_EXACT, '01 JAN 2020');
        $date->setValue(DateExactInterface::TAG_TIME, '14:30:15');

        $dateTime = $date->getDateTime();

        self::assertInstanceOf(DateTime::class, $dateTime);
        self::assertSame('2020-01-01 14:30:15', $dateTime->format('Y-m-d H:i:s'));
    }
}
