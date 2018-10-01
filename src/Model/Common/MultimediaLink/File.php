<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\MultimediaLink;

use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\FileInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The file structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class File extends DataObject implements FileInterface
{
    /**
     * @inheritDoc
     */
    public function getReference()
    {
        return $this->getValue(self::TAG_FILE_REFN);
    }

    /**
     * @inheritDoc
     */
    public function getFormat()
    {
        return $this->getValue(self::TAG_FORM);
    }
}
