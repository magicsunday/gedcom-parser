<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\GedcomInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecordInterface;

/**
 * The gedcom record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Gedcom extends DataObject implements GedcomInterface
{
    /**
     * @inheritDoc
     */
    public function getHeader(): HeaderRecordInterface
    {
        return $this->getValue(self::TAG_HEAD);
    }

    /**
     * @inheritDoc
     */
    public function getFamily()
    {
        return $this->getValue(self::TAG_FAM);
    }

    /**
     * @inheritDoc
     */
    public function getIndividual()
    {
        return $this->getValue(self::TAG_INDI);
    }

    /**
     * @inheritDoc
     */
    public function getMultimedia()
    {
        return $this->getValue(self::TAG_OBJE);
    }

    /**
     * @inheritDoc
     */
    public function getNote()
    {
        return $this->getValue(self::TAG_NOTE);
    }

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->getValue(self::TAG_REPO);
    }

    /**
     * @inheritDoc
     */
    public function getSource()
    {
        return $this->getValue(self::TAG_SOUR);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitter()
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * @inheritDoc
     */
    public function getSubmission()
    {
        return $this->getValue(self::TAG_SUBN);
    }
}
