<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\Note;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\Note\NoteStructureInterface;
use MagicSunday\Gedcom\Model\Common\Note\NoteStructure as NoteStructureModel;

/**
 * A NOTE structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NoteStructure extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [];
    }

    /**
     * Parse a NOTE structure block.
     *
     * @return NoteStructureModel
     */
    public function parse(): NoteStructureModel
    {
        $xref = $this->reader->xref();
        $note = new NoteStructureModel();

        if ($xref) {
            $note->setValue(NoteStructureInterface::TAG_XREF_NOTE, $xref);
        } else {
            $noteContent = $this->readContent();

            if ($noteContent) {
                $note->setValue(NoteStructureInterface::TAG_CONTENT, $noteContent);
            }
        }

        return $note;
    }
}
