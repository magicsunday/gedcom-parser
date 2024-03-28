<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\SubmissionRecordInterface;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The SUBN (submission) record.
 *
 * The sending system uses a submission record to send instructions and information to the receiving
 * system. TempleReady processes submission records to determine which temple the cleared records
 * should be directed to. The submission record is also used for communication between Ancestral File
 * download requests and TempleReady. Each GEDCOM transmission file should have only one
 * submission record. Multiple submissions are handled by creating separate GEDCOM transmission
 * files.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SubmissionRecord extends DataObject implements SubmissionRecordInterface
{
    use NoteTrait;
    use ChangeDateTrait;

    /**
     * {@inheritDoc}
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_SUBN);
    }

    /**
     * {@inheritDoc}
     */
    public function getSubmitterXref(): ?string
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * {@inheritDoc}
     */
    public function getFamilyFile(): ?string
    {
        return $this->getValue(self::TAG_FAMF);
    }

    /**
     * {@inheritDoc}
     */
    public function getTempleCode(): ?string
    {
        return $this->getValue(self::TAG_TEMP);
    }

    /**
     * {@inheritDoc}
     */
    public function getAncestorGenerations(): ?string
    {
        return $this->getValue(self::TAG_ANCE);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescendantGenerations(): ?string
    {
        return $this->getValue(self::TAG_DESC);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrdinanceFlag(): ?string
    {
        return $this->getValue(self::TAG_ORDI);
    }

    /**
     * {@inheritDoc}
     */
    public function getRecordIdNumber(): ?string
    {
        return $this->getValue(self::TAG_RIN);
    }
}
