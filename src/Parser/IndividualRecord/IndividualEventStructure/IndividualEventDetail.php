<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail as IndividualEventDetailModel;

/**
 * The individual event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualEventDetail extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            // Individual event details
            IndividualEventDetailModel::TAG_AGE   => Common::class,

            // Common event details
            IndividualEventDetailModel::TAG_TYPE  => Common::class,
            IndividualEventDetailModel::TAG_DATE  => Common::class,
            IndividualEventDetailModel::TAG_PLAC  => PlaceStructure::class,
            IndividualEventDetailModel::TAG_ADDR  => AddressStructure::class,
            IndividualEventDetailModel::TAG_PHON  => Common::class,
            IndividualEventDetailModel::TAG_EMAIL => Common::class,
            IndividualEventDetailModel::TAG_FAX   => Common::class,
            IndividualEventDetailModel::TAG_WWW   => Common::class,
            IndividualEventDetailModel::TAG_AGNC  => Common::class,
            IndividualEventDetailModel::TAG_RELI  => Common::class,
            IndividualEventDetailModel::TAG_CAUS  => Common::class,
            IndividualEventDetailModel::TAG_RESN  => Common::class,
            IndividualEventDetailModel::TAG_NOTE  => NoteStructure::class,
            IndividualEventDetailModel::TAG_SOUR  => SourceCitation::class,
            IndividualEventDetailModel::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     * Parse a event detail block.
     *
     * @return IndividualEventDetailModel
     */
    public function parse(): IndividualEventDetailModel
    {
        $eventDetail = new IndividualEventDetailModel();

        $this->process($eventDetail);

        return $eventDetail;
    }
}
