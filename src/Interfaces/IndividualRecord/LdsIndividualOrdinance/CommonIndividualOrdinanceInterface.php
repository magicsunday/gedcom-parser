<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;

/**
 * The common LDS individual ordinance interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface CommonIndividualOrdinanceInterface extends NoteInterface, SourceCitationInterface
{
    /**
     * LDS ordinance dates use only the Gregorian date and most often use the form of day, month, and
     * year. Only in rare instances is there a partial date.
     */
    const TAG_DATE = 'DATE';

    /**
     * An abbreviation of the temple in which LDS temple ordinances were performed.
     */
    const TAG_TEMP = 'TEMP';

    /**
     * The locality of the place where a living LDS ordinance took place. Typically, a living LDS baptism
     * place would be recorded in this field.
     */
    const TAG_PLAC = 'PLAC';

    /**
     * @return null|string
     */
    public function getDate();

    /**
     * @return null|string
     */
    public function getTempleCode();

    /**
     * @return null|string
     */
    public function getPlace();
}
