<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure\FamilyEventDetail;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure\FamilyEventDetail\Marriage as MarriageModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure\FamilyPersonAge;

/**
 * The individual BIRT (birth) event detail parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Marriage extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            // Family event details
            MarriageModel::TAG_HUSB => FamilyPersonAge::class,
            MarriageModel::TAG_WIFE => FamilyPersonAge::class,

            // Common event details
            MarriageModel::TAG_TYPE  => Common::class,
            MarriageModel::TAG_DATE  => Common::class,
            MarriageModel::TAG_PLAC  => PlaceStructure::class,
            MarriageModel::TAG_ADDR  => AddressStructure::class,
            MarriageModel::TAG_PHON  => Common::class,
            MarriageModel::TAG_EMAIL => Common::class,
            MarriageModel::TAG_FAX   => Common::class,
            MarriageModel::TAG_WWW   => Common::class,
            MarriageModel::TAG_AGNC  => Common::class,
            MarriageModel::TAG_RELI  => Common::class,
            MarriageModel::TAG_CAUS  => Common::class,
            MarriageModel::TAG_RESN  => Common::class,
            MarriageModel::TAG_NOTE  => NoteStructure::class,
            MarriageModel::TAG_SOUR  => SourceCitation::class,
            MarriageModel::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     * Parse a BIRT block.
     *
     * @return MarriageModel
     */
    public function parse(): MarriageModel
    {
        $eventDetail = new MarriageModel();
        $eventDetail->setValue(MarriageModel::TAG_FLAG, $this->reader->value());

        $this->process($eventDetail);

        return $eventDetail;
    }
}
