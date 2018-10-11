<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\LdsIndividualOrdinance\CommonIndividualOrdinance as CommonIndividualOrdinanceModel;
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
class CommonIndividualOrdinance extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            CommonIndividualOrdinanceModel::TAG_DATE => Common::class,
            CommonIndividualOrdinanceModel::TAG_TEMP => Common::class,
            CommonIndividualOrdinanceModel::TAG_PLAC => Common::class,
            CommonIndividualOrdinanceModel::TAG_STAT => CommonDateStatus::class,
            CommonIndividualOrdinanceModel::TAG_NOTE => NoteStructure::class,
            CommonIndividualOrdinanceModel::TAG_SOUR => SourceCitation::class,
        ];
    }

    /**
     * Parse a individual ordinance block.
     *
     * @return CommonIndividualOrdinanceModel
     */
    public function parse(): CommonIndividualOrdinanceModel
    {
        $ordinance = new CommonIndividualOrdinanceModel();

        $this->process($ordinance);

        return $ordinance;
    }
}
