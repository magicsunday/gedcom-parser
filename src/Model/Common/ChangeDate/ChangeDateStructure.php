<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\ChangeDate;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDate\ChangeDateStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The change date structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ChangeDateStructure extends DataObject implements ChangeDateStructureInterface
{
    use NoteTrait;

    /**
     * {@inheritDoc}
     */
    public function getDateExact(): DateExactInterface
    {
        return $this->getValue(self::TAG_DATE);
    }
}
