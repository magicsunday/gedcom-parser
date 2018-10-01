<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\SourceCitation;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Model\Common\SourceCitation\Event as EventModel;

/**
 * The SOUR-EVEN parser.
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
    public function getClassMap(): array
    {
        return [
            EventModel::TAG_ROLE => Common::class,
        ];
    }

    /**
     * Parses a SOUR-EVEN block.
     *
     * @return EventModel
     */
    public function parse(): EventModel
    {
        $event = new EventModel();
        $event->setValue(EventModel::TAG_TYPE, $this->reader->value());

        $this->process($event);

        return $event;
    }
}
