<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Individual;

use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\EventDetail as CommonEventDetail;
use MagicSunday\Gedcom\Model\Individual\EventDetail as EventDetailModel;

/**
 * The individual event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class EventDetail extends CommonEventDetail
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return parent::getClassMap()
            + [
                EventDetailModel::TAG_AGE => Common::class,
            ];
    }
}
