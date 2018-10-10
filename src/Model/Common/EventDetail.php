<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Interfaces\Common\EventDetailInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\AddressStructure;
use MagicSunday\Gedcom\Traits\Common\MultimediaLink;
use MagicSunday\Gedcom\Traits\Common\Note;
use MagicSunday\Gedcom\Traits\Common\SourceCitation;

/**
 * The event detail structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class EventDetail extends DataObject implements EventDetailInterface
{
    use AddressStructure;
    use MultimediaLink;
    use Note;
    use SourceCitation;

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getDate()
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * @inheritDoc
     */
    public function getPlace()
    {
        return $this->getValue(self::TAG_PLAC);
    }

    /**
     * @inheritDoc
     */
    public function getAgency()
    {
        return $this->getValue(self::TAG_AGNC);
    }

    /**
     * @inheritDoc
     */
    public function getReligion()
    {
        return $this->getValue(self::TAG_RELI);
    }

    /**
     * @inheritDoc
     */
    public function getCause()
    {
        return $this->getValue(self::TAG_CAUS);
    }

    /**
     * @inheritDoc
     */
    public function getRestrictionNotice()
    {
        return $this->getValue(self::TAG_RESN);
    }
}
