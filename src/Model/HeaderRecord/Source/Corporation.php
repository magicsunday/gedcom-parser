<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord\Source;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\CorporationInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\AddressStructureTrait;

/**
 * The corporation structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Corporation extends DataObject implements CorporationInterface
{
    use AddressStructureTrait;

    /**
     * @return null|string
     */
    public function getNameOfBusiness()
    {
        return $this->getValue(self::TAG_NAME_OF_BUSINESS);
    }
}
