<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
    public const TAG_AGE = 'AGE';

    /**
     * @return string
     */
    public function getAge(): string;
}
