<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\Note;

use MagicSunday\Gedcom\AbstractParser;
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
     * @inheritDoc
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
            $note->setValue(NoteStructureModel::TAG_XREF_NOTE, $xref);
        } else {
            $noteContent = $this->readContent();

            if ($noteContent) {
                $note->setValue(NoteStructureModel::TAG_CONTENT, $noteContent);
            }
        }

        return $note;
    }
}
