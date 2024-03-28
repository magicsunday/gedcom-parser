<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Interfaces\Common\EventDetailInterface;
use MagicSunday\Gedcom\Interfaces\Common\PlaceStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\AddressStructureTrait;
use MagicSunday\Gedcom\Traits\Common\MultimediaLinkTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;

/**
 * The event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class EventDetail extends DataObject implements EventDetailInterface
{
    use AddressStructureTrait;
    use MultimediaLinkTrait;
    use NoteTrait;
    use SourceCitationTrait;

    /**
     * {@inheritDoc}
     */
    public function getType(): ?string
    {
        return $this->getValue(self::TAG_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function getDate(): ?string
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getPlace(): ?PlaceStructureInterface
    {
        return $this->getValue(self::TAG_PLAC);
    }

    /**
     * {@inheritDoc}
     */
    public function getAgency(): ?string
    {
        return $this->getValue(self::TAG_AGNC);
    }

    /**
     * {@inheritDoc}
     */
    public function getReligion(): ?string
    {
        return $this->getValue(self::TAG_RELI);
    }

    /**
     * {@inheritDoc}
     */
    public function getCause(): ?string
    {
        return $this->getValue(self::TAG_CAUS);
    }

    /**
     * {@inheritDoc}
     */
    public function getRestrictionNotice(): ?string
    {
        return $this->getValue(self::TAG_RESN);
    }
}
