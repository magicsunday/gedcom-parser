<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord as IndividualModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Individual\PersonalNameStructure;

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
            IndividualModel::TAG_NAME => PersonalNameStructure::class,
            IndividualModel::TAG_SEX  => Common::class,
            IndividualModel::TAG_SUBM => Common::class,
            IndividualModel::TAG_ALIA => Common::class,
            IndividualModel::TAG_ANCI => Common::class,
            IndividualModel::TAG_DESI => Common::class,
            IndividualModel::TAG_RFN  => Common::class,
            IndividualModel::TAG_AFN  => Common::class,
            IndividualModel::TAG_RIN  => Common::class,
            IndividualModel::TAG_CHAN => ChangeDateStructure::class,
            IndividualModel::TAG_NOTE => NoteStructure::class,
            IndividualModel::TAG_SOUR => SourceCitation::class,
//            IndividualModel::TAG_OBJE => Media::class,
        ];

//        return EventStructure::getClassMap()
//            + [
//            // Event structure
//////            'BIRT' => Birth::class,
//////            'ADOP' => Adoption::class,
//////            'CHR'  => Christening::class,
////            'BAPM' => Event::class,
////            'BARM' => Event::class,
////            'BASM' => Event::class,
////            'BLES' => Event::class,
////            'BURI' => Event::class,
////            'CENS' => Event::class,
////            'CHRA' => Event::class,
////            'CONF' => Event::class,
////            'CREM' => Event::class,
////            'DEAT' => Event::class,
////            'EMIG' => Event::class,
////            'EVEN' => Event::class,
////            'FCOM' => Event::class,
////            'GRAD' => Event::class,
////            'IMMI' => Event::class,
////            'NATU' => Event::class,
////            'ORDN' => Event::class,
////            'PROB' => Event::class,
////            'RETI' => Event::class,
////            'WILL' => Event::class,
//
////             Attributes
//
//            // LDS
//
//            // Child to family link
//
//            // Spouse to family link
//
//            // Association structure
//
////            'REFN' => UserReferenceNumber::class,
//
//            'CHAN' => ChangeDate::class,
//            'NOTE' => NoteStructure::class,
//            'SOUR' => SourceCitation::class,
//            'OBJE' => Media::class,
//        ];
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
