<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord\Source;

use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\DataInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The data structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Data extends DataObject implements DataInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->getValue(self::TAG_NAME_OF_SOURCE_DATA);
    }

    /**
     * {@inheritDoc}
     */
    public function getPublicationDate(): ?DateExactInterface
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getCopyright(): ?string
    {
        return $this->getValue(self::TAG_COPR);
    }
}
