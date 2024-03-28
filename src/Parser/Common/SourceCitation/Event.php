<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\SourceCitation;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitation\EventInterface;
use MagicSunday\Gedcom\Model\Common\SourceCitation\Event as EventModel;
use MagicSunday\Gedcom\Parser\Common;

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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            EventInterface::TAG_ROLE => Common::class,
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
        $event->setValue(EventInterface::TAG_TYPE, $this->reader->value());

        $this->process($event);

        return $event;
    }
}
