<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\GedcomInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecordInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecordInterface;

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
    public function getFamily(): array
    {
        return $this->getArrayValue(self::TAG_FAM);
    }

    /**
     * @return IndividualRecordInterface[]
     */
    public function getIndividual(): array
    {
        return $this->getArrayValue(self::TAG_INDI);
    }

    /**
     * @inheritDoc
     */
    public function getMultimedia(): array
    {
        return $this->getArrayValue(self::TAG_OBJE);
    }

    /**
     * @inheritDoc
     */
    public function getNote(): array
    {
        return $this->getArrayValue(self::TAG_NOTE);
    }

    /**
     * @inheritDoc
     */
    public function getRepository(): array
    {
        return $this->getArrayValue(self::TAG_REPO);
    }

    /**
     * @inheritDoc
     */
    public function getSource(): array
    {
        return $this->getArrayValue(self::TAG_SOUR);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitter(): array
    {
        return $this->getArrayValue(self::TAG_SUBM);
    }

    /**
     * @inheritDoc
     */
    public function getSubmission()
    {
        return $this->getValue(self::TAG_SUBN);
    }
}
