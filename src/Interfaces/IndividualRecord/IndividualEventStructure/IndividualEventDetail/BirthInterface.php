<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\Interfaces\Common\FamilyChildInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;

/**
 * The individual BIRT (birth) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface BirthInterface extends IndividualEventDetailInterface
{
    /**
     * The event detail flag.
     */
    public const TAG_FLAG = 'EVENT_FLAG';

    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    public const TAG_FAMC = 'FAMC';

    /**
     * @return string|null
     */
    public function getFlag(): ?string;

    /**
     * @return FamilyChildInterface|null
     */
    public function getFamilyChild(): ?FamilyChildInterface;
}
