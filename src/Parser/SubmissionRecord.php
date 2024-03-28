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
use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\SubmissionRecordInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            SubmissionRecordInterface::TAG_SUBM => Common::class,
            SubmissionRecordInterface::TAG_FAMF => Common::class,
            SubmissionRecordInterface::TAG_TEMP => Common::class,
            SubmissionRecordInterface::TAG_ANCE => Common::class,
            SubmissionRecordInterface::TAG_DESC => Common::class,
            SubmissionRecordInterface::TAG_ORDI => Common::class,
            SubmissionRecordInterface::TAG_RIN  => Common::class,
            NoteInterface::TAG_NOTE             => NoteStructure::class,
            ChangeDateInterface::TAG_CHAN       => ChangeDateStructure::class,
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
        $submission->setValue(SubmissionRecordInterface::TAG_XREF_SUBN, $this->reader->identifier());

        $this->process($submission);

        return $submission;
    }
}
