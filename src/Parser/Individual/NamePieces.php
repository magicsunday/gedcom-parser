<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser\Individual;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\NoteStructure;
use MagicSunday\Gedcom\Model\Individual\Name as NameModel;

/**
 * A individual name pieces parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NamePieces extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
            NameModel::TAG_NPFX => Common::class,
            NameModel::TAG_GIVN => Common::class,
            NameModel::TAG_NICK => Common::class,
            NameModel::TAG_SPFX => Common::class,
            NameModel::TAG_SURN => Common::class,
            NameModel::TAG_NSFX => Common::class,
            NameModel::TAG_NOTE => NoteStructure::class,
            //            NameModel::TAG_SOUR => SourceCitation::class,
        ];
    }

    /**
     *
     * @return NameModel
     */
    public function parse(): NameModel
    {
        $name = new NameModel();

        $this->process($name);

        return $name;
    }
}
