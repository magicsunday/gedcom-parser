<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Header\Source;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\Source\Data as DataModel;
use MagicSunday\Gedcom\Parser\Common\DateExact;

/**
 * A DATA parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Data extends AbstractParser
{
    /**
     * Parses a DATA block.
     *
     * @return DataModel
     */
    public function parse(): DataModel
    {
        $data = new DataModel();
        $data->setName($this->reader->value());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
                case 'DATE':
                    $dateParser = new DateExact($this->reader, $this->logger);
                    $data->setDate($dateParser->parse());
                    break;

                case 'COPR':
                    $data->setCopyright($this->readContent());
                    break;
            }
        }

        return $data;
    }
}
