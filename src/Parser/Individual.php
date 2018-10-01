<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Individual as IndividualModel;
use MagicSunday\Gedcom\Parser\Individual\PersonalNameStructure;

/**
 * A INDI parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Individual extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
//            'RESN' => Common::class,

            // Personal name structure
            IndividualModel::TAG_NAME => PersonalNameStructure::class,

//            'SEX'  => Common::class,

            // Events
//            'BIRT' => Birth::class,
//            'ADOP' => Adoption::class,
//            'CHR'  => Christening::class,
//            'BAPM' => Event::class,
//            'BARM' => Event::class,
//            'BASM' => Event::class,
//            'BLES' => Event::class,
//            'BURI' => Event::class,
//            'CENS' => Event::class,
//            'CHRA' => Event::class,
//            'CONF' => Event::class,
//            'CREM' => Event::class,
//            'DEAT' => Event::class,
//            'EMIG' => Event::class,
//            'EVEN' => Event::class,
//            'FCOM' => Event::class,
//            'GRAD' => Event::class,
//            'IMMI' => Event::class,
//            'NATU' => Event::class,
//            'ORDN' => Event::class,
//            'PROB' => Event::class,
//            'RETI' => Event::class,
//            'WILL' => Event::class,
//            'NOTE' => NoteStructure::class,
//            'CHAN' => ChangeDate::class,

            // Attributes

            // LDS

            // Child to family link

            // Spouse to family link

//            'SUBM' => Common::class,

            // Association structure

//            'ALIA' => Common::class,
//            'ANCI' => Common::class,
//            'DESI' => Common::class,
//            'RFN'  => Common::class,
//            'AFN'  => Common::class,
//            'REFN' => UserReferenceNumber::class,

//            'RIN'  => Common::class,
//            'CHAN' => ChangeDate::class,
//            'NOTE' => NoteStructure::class,
//            'SOUR' => SourceCitation::class,
//            'OBJE' => Media::class,
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
