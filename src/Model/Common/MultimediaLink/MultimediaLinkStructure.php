<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\MultimediaLink;

use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\FileInterface;
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
     * {@inheritDoc}
     */
    public function getXref(): ?string
    {
        return $this->getValue(self::TAG_XREF_OBJE);
    }

    /**
     * {@inheritDoc}
     */
    public function getFile(): ?FileInterface
    {
        return $this->getValue(self::TAG_FILE);
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): ?string
    {
        return $this->getValue(self::TAG_TITL);
    }
}
