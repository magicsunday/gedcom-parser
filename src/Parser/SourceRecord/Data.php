<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\SourceRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\SourceRecord\DataInterface;
use MagicSunday\Gedcom\Model\SourceRecord\Data as DataModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\SourceRecord\Data\Event;

/**
 * A DATA record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Data extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            DataInterface::TAG_AGNC => Common::class,
            DataInterface::TAG_EVEN => Event::class,
            NoteInterface::TAG_NOTE => NoteStructure::class,
        ];
    }

    /**
     * Parses a DATA record block.
     *
     * @return DataModel
     */
    public function parse(): DataModel
    {
        $data = new DataModel();

        $this->process($data);

        return $data;
    }
}
