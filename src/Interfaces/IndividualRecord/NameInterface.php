<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord;

/**
 * The individual name tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface NameInterface
{
    /**
     * A list of names.
     */
    const TAG_NAME = 'NAME';

    /**
     * @return null|PersonalNameStructureInterface[]
     */
    public function getNames();
}
