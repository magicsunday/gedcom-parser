<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\Interfaces\Common\FamilyChildInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;

/**
 * The individual CHR (christening) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface ChristeningInterface extends IndividualEventDetailInterface
{
    /**
     * The event detail flag.
     */
    const TAG_FLAG = 'EVENT_FLAG';

    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    const TAG_FAMC = 'FAMC';

    /**
     * @return null|string
     */
    public function getFlag();

    /**
     * @return null|FamilyChildInterface
     */
    public function getFamilyChild();
}
