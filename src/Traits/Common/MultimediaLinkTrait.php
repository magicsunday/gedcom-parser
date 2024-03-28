<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\Common;

use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\MultimediaLinkStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;

/**
 * The multimedia link methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait MultimediaLinkTrait
{
    /**
     * @param string $key
     *
     * @return array
     */
    abstract public function getArrayValue(string $key): array;

    /**
     * @return MultimediaLinkStructureInterface[]
     */
    public function getObject(): array
    {
        return $this->getArrayValue(MultimediaLinkInterface::TAG_OBJE);
    }
}
