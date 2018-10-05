<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\SourceRecord\Data;

use MagicSunday\Gedcom\Interfaces\SourceRecord\Data\EventInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The SOUR (source), DATA (data), EVEN (event) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Event extends DataObject implements EventInterface
{
    /**
     * @inheritDoc
     */
    public function getEventsRecorded()
    {
        return $this->getValue(self::TAG_EVENTS_RECORDED);
    }

    /**
     * @inheritDoc
     */
    public function getDatePeriod()
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * @inheritDoc
     */
    public function getPlaceName()
    {
        return $this->getValue(self::TAG_PLAC);
    }
}
