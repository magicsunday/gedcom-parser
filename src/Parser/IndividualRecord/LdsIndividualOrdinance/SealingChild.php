<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\LdsIndividualOrdinance\SealingChild as SealingChildModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

/**
 * A INDI (individual) ordinance record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SealingChild extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SealingChildModel::TAG_DATE => Common::class,
            SealingChildModel::TAG_TEMP => Common::class,
            SealingChildModel::TAG_PLAC => Common::class,
            SealingChildModel::TAG_FAMC => Common::class,
            SealingChildModel::TAG_STAT => CommonDateStatus::class,
            SealingChildModel::TAG_NOTE => NoteStructure::class,
            SealingChildModel::TAG_SOUR => SourceCitation::class,
        ];
    }

    /**
     * Parse a individual ordinance block.
     *
     * @return SealingChildModel
     */
    public function parse(): SealingChildModel
    {
        $ordinance = new SealingChildModel();

        $this->process($ordinance);

        return $ordinance;
    }
}
