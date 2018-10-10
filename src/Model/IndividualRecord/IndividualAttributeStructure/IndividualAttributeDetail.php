<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\IndividualAttributeStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualAttributeStructure\IndividualAttributeDetailInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

/**
 * The individual attribute detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualAttributeDetail extends IndividualEventDetail implements IndividualAttributeDetailInterface
{
    /**
     * @return null|string
     */
    public function getDetail()
    {
        return $this->getValue(self::TAG_DETAIL);
    }
}
