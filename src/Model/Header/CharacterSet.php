<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header;

use MagicSunday\Gedcom\Interfaces\Header\CharacterSetInterface;
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
     * @inheritDoc
     */
    public function getCharacterSet()
    {
        return $this->getValue(self::TAG_CHARACTER_SET);
    }

    /**
     * @inheritDoc
     */
    public function getVersion()
    {
        return $this->getValue(self::TAG_VERS);
    }
}
