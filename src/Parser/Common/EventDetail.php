<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\EventDetailInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\EventDetail as EventDetailModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;

/**
 * The event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class EventDetail extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            EventDetailInterface::TAG_TYPE  => Common::class,
            EventDetailInterface::TAG_DATE  => Common::class,
            EventDetailInterface::TAG_PLAC  => PlaceStructure::class,
            EventDetailInterface::TAG_ADDR  => AddressStructure::class,
            EventDetailInterface::TAG_PHON  => Common::class,
            EventDetailInterface::TAG_EMAIL => Common::class,
            EventDetailInterface::TAG_FAX   => Common::class,
            EventDetailInterface::TAG_WWW   => Common::class,
            EventDetailInterface::TAG_AGNC  => Common::class,
            EventDetailInterface::TAG_RELI  => Common::class,
            EventDetailInterface::TAG_CAUS  => Common::class,
            EventDetailInterface::TAG_RESN  => Common::class,
            EventDetailInterface::TAG_NOTE  => NoteStructure::class,
            EventDetailInterface::TAG_SOUR  => SourceCitation::class,
            EventDetailInterface::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     *
     * @return EventDetailModel
     */
    public function parse(): EventDetailModel
    {
        $eventDetail = new EventDetailModel();

        $this->process($eventDetail);

        return $eventDetail;
    }
}
