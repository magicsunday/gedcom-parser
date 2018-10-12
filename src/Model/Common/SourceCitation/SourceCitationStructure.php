<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\SourceCitation;

use MagicSunday\Gedcom\Interfaces\Common\SourceCitation\SourceCitationStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\MultimediaLinkTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The source citation structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceCitationStructure extends DataObject implements SourceCitationStructureInterface
{
    use MultimediaLinkTrait;
    use NoteTrait;

    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_SOUR);
    }

    /**
     * @inheritDoc
     */
    public function getPage()
    {
        return $this->getValue(self::TAG_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function getEvent()
    {
        return $this->getValue(self::TAG_EVEN);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->getValue(self::TAG_DATA);
    }

    /**
     * @inheritDoc
     */
    public function getQuality()
    {
        return $this->getValue(self::TAG_QUAY);
    }
}
