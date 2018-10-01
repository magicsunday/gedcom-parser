<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Individual as IndividualModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate;
use MagicSunday\Gedcom\Parser\Common\NoteStructure;
use MagicSunday\Gedcom\Parser\Individual\Event;

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
//        $individual->setXref($this->reader->identifier());

        $this->process($individual);

        return $individual;
    }
}
