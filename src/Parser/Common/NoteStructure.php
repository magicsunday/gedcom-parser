<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\NoteStructure as NoteModel;
use MagicSunday\Gedcom\Parser\Common\NoteStructure as NoteStructureParser;

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
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
            NoteModel::TAG_NOTE => NoteStructureParser::class,
        ];
    }

    /**
     * Parse a NOTE structure block.
     *
     * @return NoteModel
     */
    public function parse(): NoteModel
    {
        $xref = $this->reader->xref();
        $note = new NoteModel();

        if ($xref) {
            $note->setValue(NoteModel::TAG_XREF_NOTE, $xref);
        } else {
            $noteContent = $this->readContent();

            if ($noteContent) {
                $note->setValue(NoteModel::TAG_NOTE, $noteContent);
            }
        }

        return $note;
    }
}
