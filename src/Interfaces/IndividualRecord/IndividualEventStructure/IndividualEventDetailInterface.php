<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure;

use MagicSunday\Gedcom\Interfaces\Common\EventDetailInterface;

/**
 * The family event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface IndividualEventDetailInterface extends EventDetailInterface
{
    /**
     * A number that indicates the age in years, months, and days that the principal was at the time of the
     * associated event. Any labels must come after their corresponding number, for example; 4y 8m 10d.
     */
    const TAG_AGE = 'AGE';

    /**
     * @return null|string
     */
    public function getAge();
}
