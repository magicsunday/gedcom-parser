<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\SubmissionRecord as SubmissionRecordModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;

/**
 * A SUBN (submission) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SubmissionRecord extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SubmissionRecordModel::TAG_SUBM => Common::class,
            SubmissionRecordModel::TAG_FAMF => Common::class,
            SubmissionRecordModel::TAG_TEMP => Common::class,
            SubmissionRecordModel::TAG_ANCE => Common::class,
            SubmissionRecordModel::TAG_DESC => Common::class,
            SubmissionRecordModel::TAG_ORDI => Common::class,
            SubmissionRecordModel::TAG_RIN  => Common::class,
            SubmissionRecordModel::TAG_NOTE => NoteStructure::class,
            SubmissionRecordModel::TAG_CHAN => ChangeDateStructure::class,
        ];
    }

    /**
     * Parses a SUBN block.
     *
     * @return SubmissionRecordModel
     */
    public function parse(): SubmissionRecordModel
    {
        $submission = new SubmissionRecordModel();
        $submission->setValue(SubmissionRecordModel::TAG_XREF_SUBN, $this->reader->identifier());

        $this->process($submission);

        return $submission;
    }
}
