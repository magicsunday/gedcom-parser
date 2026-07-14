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
 * The individual ADOP (adoption) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface AdoptionInterface extends IndividualEventDetailInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    public const TAG_FAMC = 'FAMC';

    public function getFamilyChild(): ?FamilyChildInterface;
}
