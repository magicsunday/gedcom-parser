<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualAttributeStructureInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructureInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinanceInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecordInterface;
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
use MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail\Adoption;
use MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail\Birth;
use MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail\Christening;
use MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail\Death;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            // Common individual tags
            IndividualRecordInterface::TAG_RESN => Common::class,
            IndividualRecordInterface::TAG_SEX  => Common::class,
            IndividualRecordInterface::TAG_SUBM => Common::class,
            IndividualRecordInterface::TAG_ALIA => Common::class,
            IndividualRecordInterface::TAG_ANCI => Common::class,
            IndividualRecordInterface::TAG_DESI => Common::class,
            IndividualRecordInterface::TAG_RFN  => Common::class,
            IndividualRecordInterface::TAG_AFN  => Common::class,
            IndividualRecordInterface::TAG_REFN => ReferenceNumber::class,
            IndividualRecordInterface::TAG_RIN  => Common::class,
            ChangeDateInterface::TAG_CHAN       => ChangeDateStructure::class,
            NoteInterface::TAG_NOTE             => NoteStructure::class,
            SourceCitationInterface::TAG_SOUR   => SourceCitation::class,
            MultimediaLinkInterface::TAG_OBJE   => MultimediaLink::class,

            // Personal name structure
            IndividualRecordInterface::TAG_NAME => PersonalNameStructure::class,

            // Individual events
            IndividualEventStructureInterface::TAG_BIRT => Birth::class,
            IndividualEventStructureInterface::TAG_CHR  => Christening::class,
            IndividualEventStructureInterface::TAG_DEAT => Death::class,
            IndividualEventStructureInterface::TAG_BURI => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_CREM => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_ADOP => Adoption::class,
            IndividualEventStructureInterface::TAG_BAPM => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_BARM => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_BASM => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_BLES => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_CHRA => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_CONF => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_FCOM => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_ORDN => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_NATU => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_EMIG => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_IMMI => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_CENS => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_PROB => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_WILL => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_GRAD => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_RETI => IndividualEventDetail::class,
            IndividualEventStructureInterface::TAG_EVEN => IndividualEventDetail::class,

            // Individual attribute structure
            IndividualAttributeStructureInterface::TAG_CAST => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_DSCR => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_EDUC => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_IDNO => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_NATI => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_NCHI => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_NMR  => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_OCCU => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_PROP => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_RELI => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_RESI => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_SSN  => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_TITL => IndividualAttributeDetail::class,
            IndividualAttributeStructureInterface::TAG_FACT => IndividualAttributeDetail::class,

            // LDS individual ordinance
            LdsIndividualOrdinanceInterface::TAG_BAPL => CommonIndividualOrdinance::class,
            LdsIndividualOrdinanceInterface::TAG_CONL => CommonIndividualOrdinance::class,
            LdsIndividualOrdinanceInterface::TAG_ENDL => CommonIndividualOrdinance::class,
            LdsIndividualOrdinanceInterface::TAG_SLGC => SealingChild::class,

            // Child to family link
            IndividualRecordInterface::TAG_FAMC => ChildToFamilyLink::class,

            // Spouse to a family link
            IndividualRecordInterface::TAG_FAMS => SpouseToFamilyLink::class,

            // Association structure
            IndividualRecordInterface::TAG_ASSO => AssociationStructure::class,
        ];
    }

    /**
     * Parse an INDI block.
     *
     * @return IndividualModel
     */
    public function parse(): IndividualModel
    {
        $individual = new IndividualModel();
        $individual->setValue(IndividualRecordInterface::TAG_XREF_INDI, $this->reader->identifier());

        $this->process($individual);

        return $individual;
    }
}
