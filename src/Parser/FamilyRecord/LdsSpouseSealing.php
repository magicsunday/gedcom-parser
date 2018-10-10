<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\FamilyRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\FamilyRecord\LdsSpouseSealing as LdsSpouseSealingModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Parser\FamilyRecord\LdsSpouseSealing\SpouseSealingDateStatus;

/**
 * A FAM (family), SLGS (LDS spouse sealing) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class LdsSpouseSealing extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            LdsSpouseSealingModel::TAG_DATE => Common::class,
            LdsSpouseSealingModel::TAG_TEMP => Common::class,
            LdsSpouseSealingModel::TAG_PLAC => Common::class,
            LdsSpouseSealingModel::TAG_STAT => SpouseSealingDateStatus::class,
            LdsSpouseSealingModel::TAG_NOTE => NoteStructure::class,
            LdsSpouseSealingModel::TAG_SOUR => SourceCitation::class,
        ];
    }

    /**
     * Parse a SLGS block.
     *
     * @return LdsSpouseSealingModel
     */
    public function parse(): LdsSpouseSealingModel
    {
        $sealing = new LdsSpouseSealingModel();

        $this->process($sealing);

        return $sealing;
    }
}
