<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\CommonIndividualOrdinanceInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\SealingChildInterface;

/**
 * The LDS individual ordinance interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface LdsIndividualOrdinanceInterface
{
    /**
     * The event of baptism performed at age eight or later by priesthood authority of the LDS Church.
     */
    const TAG_BAPL = 'BAPL';

    /**
     * The religious event by which a person receives membership in the LDS Church.
     */
    const TAG_CONL = 'CONL';

    /**
     * A religious event where an endowment ordinance for an individual was performed by priesthood
     * authority in an LDS temple.
     */
    const TAG_ENDL = 'ENDL';

    /**
     * A religious event pertaining to the sealing of a child to his or her parents in an LDS temple ceremony.
     */
    const TAG_SLGC = 'SLGC';

    /**
     * @return null|CommonIndividualOrdinanceInterface
     */
    public function getLdsBaptism();

    /**
     * @return null|CommonIndividualOrdinanceInterface
     */
    public function getLdsConfirmation();

    /**
     * @return null|CommonIndividualOrdinanceInterface
     */
    public function getLdsEndowment();

    /**
     * @return null|SealingChildInterface
     */
    public function getLdsSealingChild();
}
