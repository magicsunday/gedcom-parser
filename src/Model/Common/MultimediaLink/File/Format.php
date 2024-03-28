<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
     * {@inheritDoc}
     */
    public function getMediaFormat(): ?string
    {
        return $this->getValue(self::TAG_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function getMediaType(): ?string
    {
        return $this->getValue(self::TAG_MEDI);
    }
}
