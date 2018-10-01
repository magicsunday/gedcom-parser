<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\Individual\Name;

use MagicSunday\Gedcom\Interfaces\Individual\Name\PersonalNamePiecesInterface;

/**
 * The personal name pieces methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait PersonalNamePieces
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getValue(string $key);

    /**
     * @return null|string
     */
    public function getNamePrefix()
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NPFX);
    }

    /**
     * @return null|string
     */
    public function getGivenName()
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_GIVN);
    }

    /**
     * @return null|string
     */
    public function getNickName()
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NICK);
    }

    /**
     * @return null|string
     */
    public function getSurnamePrefix()
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_SPFX);
    }

    /**
     * @return null|string
     */
    public function getSurname()
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_SURN);
    }

    /**
     * @return null|string
     */
    public function getNameSuffix()
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NSFX);
    }
}
