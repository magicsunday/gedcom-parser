<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\AbstractParser;
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
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            FamilyEventDetailModel::TAG_HUSB => FamilyPersonAge::class,
            FamilyEventDetailModel::TAG_WIFE => FamilyPersonAge::class,

            // Common event details
            FamilyEventDetailModel::TAG_TYPE  => Common::class,
            FamilyEventDetailModel::TAG_DATE  => Common::class,
            FamilyEventDetailModel::TAG_PLAC  => PlaceStructure::class,
            FamilyEventDetailModel::TAG_ADDR  => AddressStructure::class,
            FamilyEventDetailModel::TAG_PHON  => Common::class,
            FamilyEventDetailModel::TAG_EMAIL => Common::class,
            FamilyEventDetailModel::TAG_FAX   => Common::class,
            FamilyEventDetailModel::TAG_WWW   => Common::class,
            FamilyEventDetailModel::TAG_AGNC  => Common::class,
            FamilyEventDetailModel::TAG_RELI  => Common::class,
            FamilyEventDetailModel::TAG_CAUS  => Common::class,
            FamilyEventDetailModel::TAG_RESN  => Common::class,
            FamilyEventDetailModel::TAG_NOTE  => NoteStructure::class,
            FamilyEventDetailModel::TAG_SOUR  => SourceCitation::class,
            FamilyEventDetailModel::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     * Parse a event detail block.
     *
     * @return FamilyEventDetailModel
     */
    public function parse(): FamilyEventDetailModel
    {
        $eventDetail = new FamilyEventDetailModel();
        $eventDetail->setValue(FamilyEventDetailModel::TAG_DESCRIPTOR, $this->reader->value());

        $this->process($eventDetail);

        return $eventDetail;
    }
}
