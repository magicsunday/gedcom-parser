<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Interfaces\Common\PlaceStructure\MapInterface;
use MagicSunday\Gedcom\Interfaces\Common\PlaceStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The PLAC (place) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PlaceStructure extends DataObject implements PlaceStructureInterface
{
    use NoteTrait;

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->getValue(self::TAG_PLACE_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getFormat(): ?string
    {
        return $this->getValue(self::TAG_FORM);
    }

    /**
     * {@inheritDoc}
     */
    public function getPhoneticVariation(): array
    {
        return $this->getArrayValue(self::TAG_FONE);
    }

    /**
     * {@inheritDoc}
     */
    public function getRomanizedVariation(): array
    {
        return $this->getArrayValue(self::TAG_ROMN);
    }

    /**
     * {@inheritDoc}
     */
    public function getMap(): ?MapInterface
    {
        return $this->getValue(self::TAG_MAP);
    }
}
