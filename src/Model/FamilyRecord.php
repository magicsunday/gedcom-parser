<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\FamilyRecordInterface;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\MultimediaLinkTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;

/**
 * The FAM (family) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyRecord extends FamilyEventStructure implements FamilyRecordInterface
{
    use ChangeDateTrait;
    use MultimediaLinkTrait;
    use NoteTrait;
    use SourceCitationTrait;

    /**
     * {@inheritDoc}
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_FAM);
    }

    /**
     * {@inheritDoc}
     */
    public function getRestrictionNotice(): ?string
    {
        return $this->getValue(self::TAG_RESN);
    }

    /**
     * {@inheritDoc}
     */
    public function getHusbandXref(): ?string
    {
        return $this->getValue(self::TAG_HUSB);
    }

    /**
     * {@inheritDoc}
     */
    public function getWifeXref(): ?string
    {
        return $this->getValue(self::TAG_WIFE);
    }

    /**
     * {@inheritDoc}
     */
    public function getChildrenXref(): array
    {
        return $this->getArrayValue(self::TAG_CHIL);
    }

    /**
     * {@inheritDoc}
     */
    public function getChildrenCount(): ?string
    {
        return $this->getValue(self::TAG_NCHI);
    }

    /**
     * {@inheritDoc}
     */
    public function getSubmitterXref(): array
    {
        return $this->getArrayValue(self::TAG_SUBM);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpouseSealing(): array
    {
        return $this->getArrayValue(self::TAG_SLGS);
    }

    /**
     * {@inheritDoc}
     */
    public function getReferenceNumber(): array
    {
        return $this->getArrayValue(self::TAG_REFN);
    }

    /**
     * {@inheritDoc}
     */
    public function getRecordIdNumber(): ?string
    {
        return $this->getValue(self::TAG_RIN);
    }
}
