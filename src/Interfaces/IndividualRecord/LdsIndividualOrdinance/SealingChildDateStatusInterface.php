<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance;

/**
 * The LDS individual ordinance date status interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SealingChildDateStatusInterface extends CommonChangeDateInterface
{
    /**
     * A code indicating the status of an LDS sealing child date:
     *
     * - BIC
     * - COMPLETED
     * - EXCLUDED
     * - DNS
     * - PRE-1970
     * - STILLBORN
     * - SUBMITTED
     * - UNCLEARED
     */
    public const TAG_DATE_STATUS = 'DATE_STATUS';

    /**
     * @return string|null
     */
    public function getStatus(): ?string;
}
