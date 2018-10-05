<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Submission as SubmissionModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;

/**
 * A SUBN (submission) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Submission extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SubmissionModel::TAG_SUBM => Common::class,
            SubmissionModel::TAG_FAMF => Common::class,
            SubmissionModel::TAG_TEMP => Common::class,
            SubmissionModel::TAG_ANCE => Common::class,
            SubmissionModel::TAG_DESC => Common::class,
            SubmissionModel::TAG_ORDI => Common::class,
            SubmissionModel::TAG_RIN  => Common::class,
            SubmissionModel::TAG_NOTE => NoteStructure::class,
            SubmissionModel::TAG_CHAN => ChangeDateStructure::class,
        ];
    }

    /**
     * Parses a SUBN block.
     *
     * @return SubmissionModel
     */
    public function parse(): SubmissionModel
    {
        $submission = new SubmissionModel();
        $submission->setValue(SubmissionModel::TAG_XREF_SUBN, $this->reader->identifier());

        $this->process($submission);

        return $submission;
    }
}
