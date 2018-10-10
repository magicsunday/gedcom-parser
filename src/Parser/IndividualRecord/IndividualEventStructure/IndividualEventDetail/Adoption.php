<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail\Adoption as AdoptionModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\FamilyChild;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

/**
 * The individual ADOP (adoption) event detail parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Adoption extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            // Adoption event details
            AdoptionModel::TAG_FAMC  => FamilyChild::class,

            // Individual event details
            AdoptionModel::TAG_AGE   => Common::class,

            // Common event details
            AdoptionModel::TAG_TYPE  => Common::class,
            AdoptionModel::TAG_DATE  => Common::class,
            AdoptionModel::TAG_PLAC  => PlaceStructure::class,
            AdoptionModel::TAG_ADDR  => AddressStructure::class,
            AdoptionModel::TAG_PHON  => Common::class,
            AdoptionModel::TAG_EMAIL => Common::class,
            AdoptionModel::TAG_FAX   => Common::class,
            AdoptionModel::TAG_WWW   => Common::class,
            AdoptionModel::TAG_AGNC  => Common::class,
            AdoptionModel::TAG_RELI  => Common::class,
            AdoptionModel::TAG_CAUS  => Common::class,
            AdoptionModel::TAG_RESN  => Common::class,
            AdoptionModel::TAG_NOTE  => NoteStructure::class,
            AdoptionModel::TAG_SOUR  => SourceCitation::class,
            AdoptionModel::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     * Parse a ADOP block.
     *
     * @return AdoptionModel
     */
    public function parse(): AdoptionModel
    {
        $eventDetail = new AdoptionModel();

        $this->process($eventDetail);

        return $eventDetail;
    }
}
