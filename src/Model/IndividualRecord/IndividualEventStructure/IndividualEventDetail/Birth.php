<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\BirthInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

/**
 * The individual BIRT (birth) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Birth extends IndividualEventDetail implements BirthInterface
{
    /**
     * @inheritDoc
     */
    public function getFlag()
    {
        return $this->getValue(self::TAG_FLAG);
    }

    /**
     * @inheritDoc
     */
    public function getFamilyChild()
    {
        return $this->getValue(self::TAG_FAMC);
    }
}
