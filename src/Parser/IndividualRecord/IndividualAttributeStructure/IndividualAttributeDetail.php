<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\IndividualAttributeStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualAttributeStructure\IndividualAttributeDetail as IndividualAttributeDetailModel;

/**
 * The individual attribute detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualAttributeDetail extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            // Individual event details
            IndividualAttributeDetailModel::TAG_AGE   => Common::class,

            // Common event details
            IndividualAttributeDetailModel::TAG_TYPE  => Common::class,
            IndividualAttributeDetailModel::TAG_DATE  => Common::class,
            IndividualAttributeDetailModel::TAG_PLAC  => PlaceStructure::class,
            IndividualAttributeDetailModel::TAG_ADDR  => AddressStructure::class,
            IndividualAttributeDetailModel::TAG_PHON  => Common::class,
            IndividualAttributeDetailModel::TAG_EMAIL => Common::class,
            IndividualAttributeDetailModel::TAG_FAX   => Common::class,
            IndividualAttributeDetailModel::TAG_WWW   => Common::class,
            IndividualAttributeDetailModel::TAG_AGNC  => Common::class,
            IndividualAttributeDetailModel::TAG_RELI  => Common::class,
            IndividualAttributeDetailModel::TAG_CAUS  => Common::class,
            IndividualAttributeDetailModel::TAG_RESN  => Common::class,
            IndividualAttributeDetailModel::TAG_NOTE  => NoteStructure::class,
            IndividualAttributeDetailModel::TAG_SOUR  => SourceCitation::class,
            IndividualAttributeDetailModel::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     * Parse a attribute detail block.
     *
     * @return IndividualAttributeDetailModel
     */
    public function parse(): IndividualAttributeDetailModel
    {
        $eventDetail = new IndividualAttributeDetailModel();
        $content     = $this->readContent();

        if ($content) {
            $eventDetail->setValue(IndividualAttributeDetailModel::TAG_DETAIL, $content);
        }

        $this->process($eventDetail);

        return $eventDetail;
    }
}
