<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\Interfaces\Common\FamilyChildInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;

/**
 * The individual ADOP (adoption) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface AdoptionInterface extends IndividualEventDetailInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    const TAG_FAMC = 'FAMC';

    /**
     * @return null|FamilyChildInterface
     */
    public function getFamilyChild();
}
