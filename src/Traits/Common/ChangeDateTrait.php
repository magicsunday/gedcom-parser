<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\Common;

use MagicSunday\Gedcom\Interfaces\Common\ChangeDate\ChangeDateStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;

/**
 * The change date methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait ChangeDateTrait
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getValue(string $key);

    /**
     * @return ChangeDateStructureInterface
     */
    public function getChangeDate(): ChangeDateStructureInterface
    {
        return $this->getValue(ChangeDateInterface::TAG_CHAN);
    }
}
