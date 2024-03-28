<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_BAPL = 'BAPL';

    /**
     * The religious event by which a person receives membership in the LDS Church.
     */
    public const TAG_CONL = 'CONL';

    /**
     * A religious event where an endowment ordinance for an individual was performed by priesthood
     * authority in an LDS temple.
     */
    public const TAG_ENDL = 'ENDL';

    /**
     * A religious event pertaining to the sealing of a child to his or her parents in an LDS temple ceremony.
     */
    public const TAG_SLGC = 'SLGC';

    /**
     * @return CommonIndividualOrdinanceInterface|null
     */
    public function getLdsBaptism(): ?CommonIndividualOrdinanceInterface;

    /**
     * @return CommonIndividualOrdinanceInterface|null
     */
    public function getLdsConfirmation(): ?CommonIndividualOrdinanceInterface;

    /**
     * @return CommonIndividualOrdinanceInterface|null
     */
    public function getLdsEndowment(): ?CommonIndividualOrdinanceInterface;

    /**
     * @return SealingChildInterface|null
     */
    public function getLdsSealingChild(): ?SealingChildInterface;
}
