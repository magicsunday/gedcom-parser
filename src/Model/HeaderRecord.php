<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\CharacterSetInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\GedcomInfoInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\NoteInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\PlaceInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\SourceInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecordInterface;

/**
 * The HEAD (header) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class HeaderRecord extends DataObject implements HeaderRecordInterface
{
    /**
     * {@inheritDoc}
     */
    public function getSource(): SourceInterface
    {
        return $this->getValue(self::TAG_SOUR);
    }

    /**
     * {@inheritDoc}
     */
    public function getDestination(): ?string
    {
        return $this->getValue(self::TAG_DEST);
    }

    /**
     * {@inheritDoc}
     */
    public function getTransmissionDate(): ?DateExactInterface
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getSubmitter(): string
    {
        return $this->getValue(self::TAG_SUBM);
    }

    /**
     * {@inheritDoc}
     */
    public function getSubmission(): ?string
    {
        return $this->getValue(self::TAG_SUBN);
    }

    /**
     * {@inheritDoc}
     */
    public function getFile(): ?string
    {
        return $this->getValue(self::TAG_FILE);
    }

    /**
     * {@inheritDoc}
     */
    public function getCopyright(): ?string
    {
        return $this->getValue(self::TAG_COPR);
    }

    /**
     * {@inheritDoc}
     */
    public function getGedcomInfo(): GedcomInfoInterface
    {
        return $this->getValue(self::TAG_GEDC);
    }

    /**
     * {@inheritDoc}
     */
    public function getCharacterSet(): CharacterSetInterface
    {
        return $this->getValue(self::TAG_CHAR);
    }

    /**
     * {@inheritDoc}
     */
    public function getLanguage(): ?string
    {
        return $this->getValue(self::TAG_LANG);
    }

    /**
     * {@inheritDoc}
     */
    public function getPlace(): ?PlaceInterface
    {
        return $this->getValue(self::TAG_PLAC);
    }

    /**
     * {@inheritDoc}
     */
    public function getNote(): ?NoteInterface
    {
        return $this->getValue(self::TAG_NOTE);
    }
}
