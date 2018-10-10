<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail\Birth as BirthModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

/**
 * The individual BIRT (birth) event detail parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Birth extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            // Birth event details
            BirthModel::TAG_FAMC  => Common::class,

            // Individual event details
            BirthModel::TAG_AGE   => Common::class,

            // Common event details
            BirthModel::TAG_TYPE  => Common::class,
            BirthModel::TAG_DATE  => Common::class,
            BirthModel::TAG_PLAC  => PlaceStructure::class,
            BirthModel::TAG_ADDR  => AddressStructure::class,
            BirthModel::TAG_PHON  => Common::class,
            BirthModel::TAG_EMAIL => Common::class,
            BirthModel::TAG_FAX   => Common::class,
            BirthModel::TAG_WWW   => Common::class,
            BirthModel::TAG_AGNC  => Common::class,
            BirthModel::TAG_RELI  => Common::class,
            BirthModel::TAG_CAUS  => Common::class,
            BirthModel::TAG_RESN  => Common::class,
            BirthModel::TAG_NOTE  => NoteStructure::class,
            BirthModel::TAG_SOUR  => SourceCitation::class,
            BirthModel::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     * Parse a BIRT block.
     *
     * @return BirthModel
     */
    public function parse(): BirthModel
    {
        $eventDetail = new BirthModel();
        $eventDetail->setValue(BirthModel::TAG_FLAG, $this->reader->value());

        $this->process($eventDetail);

        return $eventDetail;
    }
}
