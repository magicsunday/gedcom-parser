<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Individual;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructureInterface;
use MagicSunday\Gedcom\Parser\Individual\EventDetail\Adoption;
use MagicSunday\Gedcom\Parser\Individual\EventDetail\Birth;
use MagicSunday\Gedcom\Parser\Individual\EventDetail\Christening;

/**
 * A individual event structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class EventStructure extends EventDetail
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return parent::getClassMap()
            + [
                'ADOP' => Adoption::class,
                'BAPM' => EventDetail::class,
                'BARM' => EventDetail::class,
                'BASM' => EventDetail::class,
                IndividualEventStructureInterface::TAG_BIRT => Birth::class,
                'BLES' => EventDetail::class,
                'BURI' => EventDetail::class,
                'CENS' => EventDetail::class,
                'CHR'  => Christening::class,
                'CHRA' => EventDetail::class,
                'CONF' => EventDetail::class,
                'CREM' => EventDetail::class,
                'DEAT' => EventDetail::class,
                'EMIG' => EventDetail::class,
                'EVEN' => EventDetail::class,
                'FCOM' => EventDetail::class,
                'GRAD' => EventDetail::class,
                'IMMI' => EventDetail::class,
                'NATU' => EventDetail::class,
                'ORDN' => EventDetail::class,
                'PROB' => EventDetail::class,
                'RETI' => EventDetail::class,
                'WILL' => EventDetail::class,
            ];
    }
}
