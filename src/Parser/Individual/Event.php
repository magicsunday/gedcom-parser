<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Individual;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Individual\Event as EventModel;
use MagicSunday\Gedcom\Parser\Common\NoteStructure;

/**
 * A individual event parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Event extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [

        ];
    }

    /**
     *
     * @return EventModel
     */
    public function parse(): EventModel
    {
        $event = new EventModel();
        $event->setType($this->reader->tag())
            ->setValue($this->reader->value());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'TYPE':
                    break;

                case 'DATE':
                    break;

                case 'PLAC':
                    break;

                case 'ADDR':
                    break;

                case 'PHON':
                    break;

                case 'EMAIL':
                    break;

                case 'FAX':
                    break;

                case 'WWW':
                    break;

                case 'AGNC':
                    break;

                case 'RELI':
                    break;

                case 'CAUS':
                    break;

                case 'RESN':
                    break;

                case 'NOTE':
                    $noteStructureParser = new NoteStructure($this->reader, $this->logger);
                    $event->addNote($noteStructureParser->parse());
                    break;

                case 'SOUR':
                    break;

                case 'OBJE':
                    break;

                case 'AGE':
                    $event->setAge($this->reader->value());
                    break;
            }
        }

        return $event;
    }
}
