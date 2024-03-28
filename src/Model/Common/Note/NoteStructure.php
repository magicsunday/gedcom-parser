<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\Note;

use MagicSunday\Gedcom\Interfaces\Common\Note\NoteStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * A note structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NoteStructure extends DataObject implements NoteStructureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getXref(): ?string
    {
        return $this->getValue(self::TAG_XREF_NOTE);
    }

    /**
     * {@inheritDoc}
     */
    public function getContent(): ?string
    {
        return $this->getValue(self::TAG_CONTENT);
    }
}
