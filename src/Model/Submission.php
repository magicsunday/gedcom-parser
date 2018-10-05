<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\SubmissionInterface;
use MagicSunday\Gedcom\Traits\Common\ChangeDate;
use MagicSunday\Gedcom\Traits\Common\Note;

/**
 * The SUBN (submission) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Submission extends DataObject implements SubmissionInterface
{
    use Note;
    use ChangeDate;

    /**
     * @inheritDoc
     */
    public function getSubmissionXref()
    {
        return $this->getValue(self::TAG_XREF_SUBN);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitterXref()
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * @inheritDoc
     */
    public function getFamilyFile()
    {
        return $this->getValue(self::TAG_FAMF);
    }

    /**
     * @inheritDoc
     */
    public function getTempleCode()
    {
        return $this->getValue(self::TAG_TEMP);
    }

    /**
     * @inheritDoc
     */
    public function getAncestorGenerations()
    {
        return $this->getValue(self::TAG_ANCE);
    }

    /**
     * @inheritDoc
     */
    public function getDescendantGenerations()
    {
        return $this->getValue(self::TAG_DESC);
    }

    /**
     * @inheritDoc
     */
    public function getOrdinanceFlag()
    {
        return $this->getValue(self::TAG_ORDI);
    }

    /**
     * @inheritDoc
     */
    public function getRecordIdNumber()
    {
        return $this->getValue(self::TAG_RIN);
    }
}
