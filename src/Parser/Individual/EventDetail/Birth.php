<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Individual\EventDetail;

use MagicSunday\Gedcom\Parser\Common\FamilyChild;
use MagicSunday\Gedcom\Parser\Individual\EventDetail;

/**
 * The individual event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Birth extends EventDetail
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return parent::getClassMap()
            + [
                'FAMC' => FamilyChild::class,
            ];
    }
}
