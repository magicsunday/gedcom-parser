<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
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
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            HeaderRecordModel::TAG_SOUR => Source::class,
            HeaderRecordModel::TAG_DEST => Common::class,
            HeaderRecordModel::TAG_DATE => DateExact::class,
            HeaderRecordModel::TAG_SUBM => Common::class,
            HeaderRecordModel::TAG_SUBN => Common::class,
            HeaderRecordModel::TAG_FILE => Common::class,
            HeaderRecordModel::TAG_COPR => Common::class,
            HeaderRecordModel::TAG_GEDC => GedcomInfo::class,
            HeaderRecordModel::TAG_CHAR => CharacterSet::class,
            HeaderRecordModel::TAG_LANG => Common::class,
            HeaderRecordModel::TAG_PLAC => Place::class,
            HeaderRecordModel::TAG_NOTE => Note::class,
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
