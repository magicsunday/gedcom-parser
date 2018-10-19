<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\Common;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\Note\NoteStructureInterface;

/**
 * The note methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait NoteTrait
{
    /**
     * @param string $key
     *
     * @return array
     */
    abstract public function getArrayValue(string $key): array;

    /**
     * @return NoteStructureInterface[]
     */
    public function getNote(): array
    {
        return $this->getArrayValue(NoteInterface::TAG_NOTE);
    }
}
