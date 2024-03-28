<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\HeaderRecordInterface;
use MagicSunday\Gedcom\Model\HeaderRecord as HeaderRecordModel;
use MagicSunday\Gedcom\Parser\Common\DateExact;
use MagicSunday\Gedcom\Parser\HeaderRecord\CharacterSet;
use MagicSunday\Gedcom\Parser\HeaderRecord\GedcomInfo;
use MagicSunday\Gedcom\Parser\HeaderRecord\Note;
use MagicSunday\Gedcom\Parser\HeaderRecord\Place;
use MagicSunday\Gedcom\Parser\HeaderRecord\Source;

/**
 * A HEAD record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class HeaderRecord extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            HeaderRecordInterface::TAG_SOUR => Source::class,
            HeaderRecordInterface::TAG_DEST => Common::class,
            HeaderRecordInterface::TAG_DATE => DateExact::class,
            HeaderRecordInterface::TAG_SUBM => Common::class,
            HeaderRecordInterface::TAG_SUBN => Common::class,
            HeaderRecordInterface::TAG_FILE => Common::class,
            HeaderRecordInterface::TAG_COPR => Common::class,
            HeaderRecordInterface::TAG_GEDC => GedcomInfo::class,
            HeaderRecordInterface::TAG_CHAR => CharacterSet::class,
            HeaderRecordInterface::TAG_LANG => Common::class,
            HeaderRecordInterface::TAG_PLAC => Place::class,
            HeaderRecordInterface::TAG_NOTE => Note::class,
        ];
    }

    /**
     * Parses a HEAD block.
     *
     * @return HeaderRecordModel
     */
    public function parse(): HeaderRecordModel
    {
        $header = new HeaderRecordModel();

        $this->process($header);

        //        if (($header->getGedcomInfo() !== null)
        //            && ($header->getGedcomInfo()->getVersion() !== '5.5.1')
        //        ) {
        //            $this->logger->warning('Wrong gedcom version. Must be 5.5.1');
        //        }

        return $header;
    }
}
