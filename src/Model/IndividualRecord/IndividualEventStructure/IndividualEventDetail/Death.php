<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\DeathInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

/**
 * The individual DEAT (death) event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Death extends IndividualEventDetail implements DeathInterface
{
    /**
     * @inheritDoc
     */
    public function getFlag()
    {
        return $this->getValue(self::TAG_FLAG);
    }
}
