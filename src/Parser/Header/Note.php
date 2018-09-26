<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Header;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\Note as NoteModel;

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
     * Parses a NOTE block.
     *
     * @return NoteModel
     */
    public function parse(): NoteModel
    {
        $note = new NoteModel();
        $note->setNote($this->reader->value());

        $content = '';

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->type()) {
                case 'CONT':
                    if ($content !== '') {
                        $content .= "\n";
                    }

                    $content .= $this->reader->value();
                    break;

                case 'CONC':
                    $content .= $this->reader->value();
                    break;
            }
        }

        if ($content !== '') {
            $note->setContent($content);
        }

        return $note;
    }
}
