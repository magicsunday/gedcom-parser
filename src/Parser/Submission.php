<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Submission as SubmissionModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate;

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
            switch ($this->reader->tag()) {
                case 'SUBM':
                    $submission->setSubmitter($this->reader->value());
                    break;

                case 'FAMF':
                    $submission->setFamilyFile($this->reader->value());
                    break;

                case 'TEMP':
                    $submission->setTempleCode($this->reader->value());
                    break;

                case 'ANCE':
                    $submission->setAncestorGenerations($this->reader->value());
                    break;

                case 'DESC':
                    $submission->setDescendantGenerations($this->reader->value());
                    break;

                case 'ORDI':
                    $submission->setOrdinanceFlag($this->reader->value() === 'yes');
                    break;

                case 'RIN':
                    $submission->setRecordIdentificationNumber($this->reader->value());
                    break;

                case 'NOTE':
                    break;

                case 'CHAN':
                    $changeDateParser = new ChangeDate($this->reader, $this->logger);
                    $submission->setChangeDate($changeDateParser->parse());
                    break;
            }
        }

        return $submission;
    }
}
