<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\SourceRecord\DataInterface;
use MagicSunday\Gedcom\Interfaces\SourceRecordInterface;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\MultimediaLinkTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The SOUR (source) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceRecord extends DataObject implements SourceRecordInterface
{
    use ChangeDateTrait;
    use MultimediaLinkTrait;
    use NoteTrait;

    /**
     * {@inheritDoc}
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_SOUR);
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ?DataInterface
    {
        return $this->getValue(self::TAG_DATA);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthor(): ?string
    {
        return $this->getValue(self::TAG_AUTH);
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): ?string
    {
        return $this->getValue(self::TAG_TITL);
    }

    /**
     * {@inheritDoc}
     */
    public function getAbbreviation(): ?string
    {
        return $this->getValue(self::TAG_ABBR);
    }

    /**
     * {@inheritDoc}
     */
    public function getPublication(): ?string
    {
        return $this->getValue(self::TAG_PUBL);
    }

    /**
     * {@inheritDoc}
     */
    public function getText(): ?string
    {
        return $this->getValue(self::TAG_TEXT);
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(): array
    {
        return $this->getArrayValue(self::TAG_REPO);
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
