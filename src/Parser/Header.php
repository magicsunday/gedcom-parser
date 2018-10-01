<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header as HeaderModel;
use MagicSunday\Gedcom\Parser\Common\DateExact;
use MagicSunday\Gedcom\Parser\Header\CharacterSet;
use MagicSunday\Gedcom\Parser\Header\GedcomInfo;
use MagicSunday\Gedcom\Parser\Header\Note;
use MagicSunday\Gedcom\Parser\Header\Place;
use MagicSunday\Gedcom\Parser\Header\Source;

/**
 * A HEAD record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Header extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            HeaderModel::TAG_SOUR => Source::class,
            HeaderModel::TAG_DEST => Common::class,
            HeaderModel::TAG_DATE => DateExact::class,
            HeaderModel::TAG_SUBM => Common::class,
            HeaderModel::TAG_SUBN => Common::class,
            HeaderModel::TAG_FILE => Common::class,
            HeaderModel::TAG_COPR => Common::class,
            HeaderModel::TAG_GEDC => GedcomInfo::class,
            HeaderModel::TAG_CHAR => CharacterSet::class,
            HeaderModel::TAG_LANG => Common::class,
            HeaderModel::TAG_PLAC => Place::class,
            HeaderModel::TAG_NOTE => Note::class,
        ];
    }

    /**
     * Parses a HEAD block.
     *
     * @return HeaderModel
     */
    public function parse(): HeaderModel
    {
        $header = new HeaderModel();

        $this->process($header);

        return $header;
    }
}
