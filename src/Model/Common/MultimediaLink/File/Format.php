<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\MultimediaLink\File;

use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\File\FormatInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The form structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Format extends DataObject implements FormatInterface
{
    /**
     * @inheritDoc
     */
    public function getMediaFormat()
    {
        return $this->getValue(self::TAG_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getMediaType()
    {
        return $this->getValue(self::TAG_MEDI);
    }
}
