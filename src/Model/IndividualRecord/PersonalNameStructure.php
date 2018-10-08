<?php
declare(strict_types=1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\Name\NamePhoneticVariationInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\Name\NameRomanizedVariationInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructureInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\Name\PersonalNamePieces;

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
     * @return null|string
     */
    public function getName()
    {
        return $this->getValue(self::TAG_NAME_PERSONAL);
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }

    /**
     * @return null|NamePhoneticVariationInterface
     */
    public function getPhoneticVariation()
    {
        return $this->getValue(self::TAG_FONE);
    }

    /**
     * @return null|NameRomanizedVariationInterface
     */
    public function getRomanizedVariation()
    {
        return $this->getValue(self::TAG_ROMN);
    }
}
