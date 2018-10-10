<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;

/**
 * The individual DEAT (death) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface DeathInterface extends IndividualEventDetailInterface
{
    /**
     * The event detail flag.
     */
    const TAG_FLAG = 'EVENT_FLAG';

    /**
     * @return null|string
     */
    public function getFlag();
}
