<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructure\FamilyPersonAgeInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\ValueObject\AgeValue;

use function is_string;

/**
 * The family person AGE structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyPersonAge extends DataObject implements FamilyPersonAgeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAge(): string
    {
        return $this->getValue(self::TAG_AGE);
    }

    /**
     * {@inheritDoc}
     */
    public function getAgeValue(): ?AgeValue
    {
        $age = $this->getValue(self::TAG_AGE);

        return is_string($age) ? AgeValue::fromGedcom($age) : null;
    }
}
