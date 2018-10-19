<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructureInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure\PersonalNamePieces;

/**
 * The personal name structure model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PersonalNameStructure extends PersonalNamePieces implements PersonalNameStructureInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getValue(self::TAG_NAME_PERSONAL);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getPhoneticVariation(): array
    {
        return $this->getArrayValue(self::TAG_FONE);
    }

    /**
     * @inheritDoc
     */
    public function getRomanizedVariation(): array
    {
        return $this->getArrayValue(self::TAG_ROMN);
    }
}
