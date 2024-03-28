<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\PersonalNamePiecesInterface;

/**
 * The personal name pieces methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait PersonalNamePiecesTrait
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getValue(string $key);

    /**
     * @return string|null
     */
    public function getNamePrefix(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NPFX);
    }

    /**
     * @return string|null
     */
    public function getGivenName(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_GIVN);
    }

    /**
     * @return string|null
     */
    public function getNickName(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NICK);
    }

    /**
     * @return string|null
     */
    public function getSurnamePrefix(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_SPFX);
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_SURN);
    }

    /**
     * @return string|null
     */
    public function getNameSuffix(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NSFX);
    }
}
