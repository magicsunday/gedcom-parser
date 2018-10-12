<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\FamilyRecord;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\LdsSpouseSealing\SpouseSealingDateStatusInterface;

/**
 * The LDS spouse sealing interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface LdsSpouseSealingInterface extends
    NoteInterface,
    SourceCitationInterface
{
    /**
     * LDS ordinance dates use only the Gregorian date and most often use the form of day, month, and
     * year. Only in rare instances is there a partial date. The temple tag and code should always accompany
     * temple ordinance dates. Sometimes the LDS_(ordinance)_DATE_STATUS is used to indicate that an
     * ordinance date and temple code is not required, such as when BIC is used.
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
     * The spouse sealing date status.
     */
    const TAG_STAT = 'STAT';

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

    /**
     * @return null|SpouseSealingDateStatusInterface
     */
    public function getDateStatus();
}
