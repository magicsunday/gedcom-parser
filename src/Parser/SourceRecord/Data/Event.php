<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\SourceRecord\Data;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\SourceRecord\Data\Event as EventModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A EVEN record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Event extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            EventModel::TAG_DATE => Common::class,
            EventModel::TAG_PLAC => Common::class,
        ];
    }

    /**
     * Parses a EVEN record block.
     *
     * @return EventModel
     */
    public function parse(): EventModel
    {
        $event = new EventModel();
        $event->setValue(EventModel::TAG_EVENTS_RECORDED, $this->reader->value());

        $this->process($event);

        return $event;
    }
}
