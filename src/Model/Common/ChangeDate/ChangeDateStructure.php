<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\ChangeDate;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDate\ChangeDateStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\Note;

/**
 * The change date structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ChangeDateStructure extends DataObject implements ChangeDateStructureInterface
{
    use Note;

    /**
     * @inheritDoc
     */
    public function getDateExact()
    {
        return $this->getValue(self::TAG_DATE);
    }
}
