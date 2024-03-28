<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\AddressStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\EventDetailInterface;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyEventDetailInterface;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure\FamilyEventDetail as FamilyEventDetailModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

/**
 * A FAM (family) event detail structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyEventDetail extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            // Family event details
            FamilyEventDetailInterface::TAG_HUSB => FamilyPersonAge::class,
            FamilyEventDetailInterface::TAG_WIFE => FamilyPersonAge::class,

            // Common event details
            EventDetailInterface::TAG_TYPE       => Common::class,
            EventDetailInterface::TAG_DATE       => Common::class,
            EventDetailInterface::TAG_PLAC       => PlaceStructure::class,
            AddressStructureInterface::TAG_ADDR  => AddressStructure::class,
            AddressStructureInterface::TAG_PHON  => Common::class,
            AddressStructureInterface::TAG_EMAIL => Common::class,
            AddressStructureInterface::TAG_FAX   => Common::class,
            AddressStructureInterface::TAG_WWW   => Common::class,
            EventDetailInterface::TAG_AGNC       => Common::class,
            EventDetailInterface::TAG_RELI       => Common::class,
            EventDetailInterface::TAG_CAUS       => Common::class,
            EventDetailInterface::TAG_RESN       => Common::class,
            NoteInterface::TAG_NOTE              => NoteStructure::class,
            SourceCitationInterface::TAG_SOUR    => SourceCitation::class,
            MultimediaLinkInterface::TAG_OBJE    => MultimediaLink::class,
        ];
    }

    /**
     * Parse an event detail block.
     *
     * @return FamilyEventDetailModel
     */
    public function parse(): FamilyEventDetailModel
    {
        $eventDetail = new FamilyEventDetailModel();

        $this->process($eventDetail);

        return $eventDetail;
    }
}
