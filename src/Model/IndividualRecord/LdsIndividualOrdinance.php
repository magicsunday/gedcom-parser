<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinanceInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\IndividualRecord\LdsIndividualOrdinance as LdsIndividualOrdinanceTrait;

/**
 * The LDS individual ordinance.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class LdsIndividualOrdinance extends DataObject implements LdsIndividualOrdinanceInterface
{
    use LdsIndividualOrdinanceTrait;
}
