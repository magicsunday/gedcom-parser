<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\MultimediaLink;

use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\MultimediaLinkStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The media structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class MultimediaLinkStructure extends DataObject implements MultimediaLinkStructureInterface
{
    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_OBJE);
    }

    /**
     * @inheritDoc
     */
    public function getFile()
    {
        return $this->getValue(self::TAG_FILE);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getValue(self::TAG_TITL);
    }
}
