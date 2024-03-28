<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\CharacterSetInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The character set structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class CharacterSet extends DataObject implements CharacterSetInterface
{
    /**
     * {@inheritDoc}
     */
    public function getCharacterSet(): string
    {
        return $this->getValue(self::TAG_CHARACTER_SET);
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion(): ?string
    {
        return $this->getValue(self::TAG_VERS);
    }
}
