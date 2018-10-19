<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure;

/**
 * The family person AGE structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface FamilyPersonAgeInterface
{
    /**
     * The age at the event.
     */
    const TAG_AGE = 'AGE';

    /**
     * @return string
     */
    public function getAge(): string;
}
