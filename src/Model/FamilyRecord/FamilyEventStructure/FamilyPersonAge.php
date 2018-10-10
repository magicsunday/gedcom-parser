<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyPersonAgeInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The family person AGE structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyPersonAge extends DataObject implements FamilyPersonAgeInterface
{
    /**
     * @inheritDoc
     */
    public function getAge()
    {
        return $this->getValue(self::TAG_AGE);
    }
}
