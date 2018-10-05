<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\HeaderRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\HeaderRecord\Note as NoteModel;

/**
 * A NOTE parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Note extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [];
    }

    /**
     * Parses a NOTE block.
     *
     * @return NoteModel
     */
    public function parse(): NoteModel
    {
        $note    = new NoteModel();
        $content = $this->readContent();

        if ($content !== '') {
            $note->setValue(NoteModel::TAG_GEDCOM_CONTENT_DESCRIPTION, $content);
        }

        return $note;
    }
}
