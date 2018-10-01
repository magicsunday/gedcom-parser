<?php
declare(strict_types=1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Model\Individual\PersonalNameStructure;

use MagicSunday\Gedcom\Model\DataObject;

/**
 * The personal name pieces.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PersonalNamePieces extends DataObject
{
    /**
     * Text which appears on a name line before the given and surname parts of a name.
     *   i.e. (Lt. Cmndr.) Joseph /Allen/ jr.
     *
     * In this example Lt. Cmndr. is considered as the name prefix portion.
     */
    const TAG_NPFX = 'NPFX';

    /**
     * A given or earned name used for official identification of a person.
     */
    const TAG_GIVN = 'GIVN';

    /**
     * A descriptive or familiar that is used instead of, or in addition to, one's proper name.
     */
    const TAG_NICK = 'NICK';

    /**
     * A name piece used as a non-indexing pre-part of a surname.
     */
    const TAG_SPFX = 'SPFX';

    /**
     * A family name passed on or used by members of a family.
     */
    const TAG_SURN = 'SURN';

    /**
     * Text which appears on a name line after or behind the given and surname parts of a name.
     *   i.e. Lt. Cmndr. Joseph /Allen/ (jr.)
     *
     * In this example jr. is considered as the name suffix portion.
     */
    const TAG_NSFX = 'NSFX';

    /**
     * A list of notes assigned to the name.
     */
    const TAG_NOTE = 'NOTE';

    /**
     * A list of sources assigned to the name.
     */
    const TAG_SOUR = 'SOUR';

    /**
     * @return null|string
     */
    public function getNamePrefix()
    {
        return $this->getValue(self::TAG_NPFX);
    }

    /**
     * @return null|string
     */
    public function getGivenName()
    {
        return $this->getValue(self::TAG_GIVN);
    }

    /**
     * @return null|string
     */
    public function getNickname()
    {
        return $this->getValue(self::TAG_NICK);
    }

    /**
     * @return null|string
     */
    public function getSurnamePrefix()
    {
        return $this->getValue(self::TAG_SPFX);
    }

    /**
     * @return null|string
     */
    public function getSurname()
    {
        return $this->getValue(self::TAG_SURN);
    }

    /**
     * @return null|string
     */
    public function getNameSuffix()
    {
        return $this->getValue(self::TAG_NSFX);
    }

    /**
     * @return array
     */
    public function getNotes()
    {
        return $this->getValue(self::TAG_NOTE);
    }

    /**
     * @return array
     */
    public function getSources()
    {
        return $this->getValue(self::TAG_SOUR);
    }
}
