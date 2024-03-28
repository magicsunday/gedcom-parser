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
 * The LDS individual ordinance sealing child (SLGC) interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SealingChildInterface extends CommonIndividualOrdinanceInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    public const TAG_FAMC = 'FAMC';

    /**
     * @return string|null
     */
    public function getFamilyXref(): ?string;

    /**
     * @return SealingChildDateStatusInterface|null
     */
    public function getDateStatus(): ?SealingChildDateStatusInterface;
}
