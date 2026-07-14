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
use MagicSunday\Gedcom\ValueObject\PlaceValue;

use function is_string;
use function trim;

/**
 * The PLAC (place) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
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
    public function getPlaceValue(): ?PlaceValue
    {
        $name = $this->getValue(self::TAG_PLACE_NAME);

        if (!is_string($name) || (trim($name) === '')) {
            return null;
        }

        $form = $this->getValue(self::TAG_FORM);

        return PlaceValue::fromGedcom($name, is_string($form) ? $form : null);
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
