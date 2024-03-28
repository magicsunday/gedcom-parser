<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\LdsIndividualOrdinance;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance\CommonIndividualOrdinanceInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            CommonIndividualOrdinanceInterface::TAG_DATE => Common::class,
            CommonIndividualOrdinanceInterface::TAG_TEMP => Common::class,
            CommonIndividualOrdinanceInterface::TAG_PLAC => Common::class,
            CommonIndividualOrdinanceInterface::TAG_STAT => CommonDateStatus::class,
            NoteInterface::TAG_NOTE                      => NoteStructure::class,
            SourceCitationInterface::TAG_SOUR            => SourceCitation::class,
        ];
    }

    /**
     * Parse an individual ordinance block.
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
