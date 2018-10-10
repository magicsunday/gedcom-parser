<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord as IndividualModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\ReferenceNumber;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

/**
 * A INDI record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualRecord extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            IndividualModel::TAG_RESN => Common::class,

            // Personal name structure
            //IndividualModel::TAG_NAME => PersonalNameStructure::class,

            IndividualModel::TAG_SEX  => Common::class,

            // Individual events
            IndividualModel::TAG_BIRT => IndividualEventDetail\Birth::class,
            IndividualModel::TAG_CHR  => IndividualEventDetail\Christening::class,
            IndividualModel::TAG_DEAT => IndividualEventDetail\Death::class,
            IndividualModel::TAG_BURI => IndividualEventDetail::class,
            IndividualModel::TAG_CREM => IndividualEventDetail::class,
            IndividualModel::TAG_ADOP => IndividualEventDetail\Adoption::class,
            IndividualModel::TAG_BAPM => IndividualEventDetail::class,
            IndividualModel::TAG_BARM => IndividualEventDetail::class,
            IndividualModel::TAG_BASM => IndividualEventDetail::class,
            IndividualModel::TAG_BLES => IndividualEventDetail::class,
            IndividualModel::TAG_CHRA => IndividualEventDetail::class,
            IndividualModel::TAG_CONF => IndividualEventDetail::class,
            IndividualModel::TAG_FCOM => IndividualEventDetail::class,
            IndividualModel::TAG_ORDN => IndividualEventDetail::class,
            IndividualModel::TAG_NATU => IndividualEventDetail::class,
            IndividualModel::TAG_EMIG => IndividualEventDetail::class,
            IndividualModel::TAG_IMMI => IndividualEventDetail::class,
            IndividualModel::TAG_CENS => IndividualEventDetail::class,
            IndividualModel::TAG_PROB => IndividualEventDetail::class,
            IndividualModel::TAG_WILL => IndividualEventDetail::class,
            IndividualModel::TAG_GRAD => IndividualEventDetail::class,
            IndividualModel::TAG_RETI => IndividualEventDetail::class,
            IndividualModel::TAG_EVEN => IndividualEventDetail::class,

            // Individual attribute structure
            // LDS individual ordinance
            // Child to family link
            // Spouse to family link

            IndividualModel::TAG_SUBM => Common::class,

            // Association structure

            IndividualModel::TAG_ALIA => Common::class,
            IndividualModel::TAG_ANCI => Common::class,
            IndividualModel::TAG_DESI => Common::class,
            IndividualModel::TAG_RFN  => Common::class,
            IndividualModel::TAG_AFN  => Common::class,
            IndividualModel::TAG_REFN => ReferenceNumber::class,
            IndividualModel::TAG_RIN  => Common::class,
            IndividualModel::TAG_CHAN => ChangeDateStructure::class,
            IndividualModel::TAG_NOTE => NoteStructure::class,
            IndividualModel::TAG_SOUR => SourceCitation::class,
            IndividualModel::TAG_OBJE => MultimediaLink::class,
        ];
    }

    /**
     * Parse a INDI block.
     *
     * @return IndividualModel
     */
    public function parse(): IndividualModel
    {
        $individual = new IndividualModel();
        $individual->setValue(IndividualModel::TAG_XREF_INDI, $this->reader->identifier());

        $this->process($individual);

        return $individual;
    }
}
