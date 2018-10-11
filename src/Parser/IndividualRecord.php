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
use MagicSunday\Gedcom\Parser\IndividualRecord\AssociationStructure;
use MagicSunday\Gedcom\Parser\IndividualRecord\ChildToFamilyLink;
use MagicSunday\Gedcom\Parser\IndividualRecord\IndividualAttributeStructure\IndividualAttributeDetail;
use MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail;
use MagicSunday\Gedcom\Parser\IndividualRecord\LdsIndividualOrdinance\CommonIndividualOrdinance;
use MagicSunday\Gedcom\Parser\IndividualRecord\LdsIndividualOrdinance\SealingChild;
use MagicSunday\Gedcom\Parser\IndividualRecord\PersonalNameStructure;
use MagicSunday\Gedcom\Parser\IndividualRecord\SpouseToFamilyLink;

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
            // Common individual tags
            IndividualModel::TAG_RESN => Common::class,
            IndividualModel::TAG_SEX  => Common::class,
            IndividualModel::TAG_SUBM => Common::class,
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

            // Personal name structure
            IndividualModel::TAG_NAME => PersonalNameStructure::class,

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
            IndividualModel::TAG_CAST => IndividualAttributeDetail::class,
            IndividualModel::TAG_DSCR => IndividualAttributeDetail::class,
            IndividualModel::TAG_EDUC => IndividualAttributeDetail::class,
            IndividualModel::TAG_IDNO => IndividualAttributeDetail::class,
            IndividualModel::TAG_NATI => IndividualAttributeDetail::class,
            IndividualModel::TAG_NCHI => IndividualAttributeDetail::class,
            IndividualModel::TAG_NMR  => IndividualAttributeDetail::class,
            IndividualModel::TAG_OCCU => IndividualAttributeDetail::class,
            IndividualModel::TAG_PROP => IndividualAttributeDetail::class,
            IndividualModel::TAG_RELI => IndividualAttributeDetail::class,
            IndividualModel::TAG_RESI => IndividualAttributeDetail::class,
            IndividualModel::TAG_SSN  => IndividualAttributeDetail::class,
            IndividualModel::TAG_TITL => IndividualAttributeDetail::class,
            IndividualModel::TAG_FACT => IndividualAttributeDetail::class,

            // LDS individual ordinance
            IndividualModel::TAG_BAPL => CommonIndividualOrdinance::class,
            IndividualModel::TAG_CONL => CommonIndividualOrdinance::class,
            IndividualModel::TAG_ENDL => CommonIndividualOrdinance::class,
            IndividualModel::TAG_SLGC => SealingChild::class,

            // Child to family link
            IndividualModel::TAG_FAMC => ChildToFamilyLink::class,

            // Spouse to family link
            IndividualModel::TAG_FAMS => SpouseToFamilyLink::class,

            // Association structure
            IndividualModel::TAG_ASSO => AssociationStructure::class,
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
