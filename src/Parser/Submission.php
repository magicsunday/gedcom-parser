<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Submission as SubmissionModel;

/**
 * A SUBN parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Submission extends AbstractParser
{
    /**
     * Parses a SUBN block.
     *
     * @return SubmissionModel
     */
    public function parse(): SubmissionModel
    {
        $submission = new SubmissionModel();
        $submission->setSubmitter($this->reader->identifier());

        while ($this->reader->read() && $this->valid()) {
            if ($this->reader->type() === 'SUBM') {
                $submission->setSubmitter($this->reader->value());
            }

            if ($this->reader->type() === 'FAMF') {
                $submission->setFamilyFile($this->reader->value());
            }

            if ($this->reader->type() === 'TEMP') {
                $submission->setTempleCode($this->reader->value());
            }

            if ($this->reader->type() === 'ANCE') {
                $submission->setAncestorGenerations($this->reader->value());
            }

            if ($this->reader->type() === 'DESC') {
                $submission->setDescendantGenerations($this->reader->value());
            }

            if ($this->reader->type() === 'ORDI') {
                $submission->setOrdinanceFlag($this->reader->value() === 'yes');
            }

            if ($this->reader->type() === 'RIN') {
                $submission->setRecordIdentificationNumber($this->reader->value());
            }

            if ($this->reader->type() === 'NOTE') {
                //
            }

            if ($this->reader->type() === 'CHAN') {
                $changeDateParser = new ChangeDate($this->reader, $this->logger);
                $changeDate       = $changeDateParser->parse();

                $submission->setChangeDate($changeDate);
            }
        }

        return $submission;
    }
}
