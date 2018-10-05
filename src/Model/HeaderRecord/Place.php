<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\PlaceInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The place structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Place extends DataObject implements PlaceInterface
{
    /**
     * @inheritDoc
     */
    public function getForm()
    {
        return $this->getValue(self::TAG_FORM);
    }
}
