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
 * @license https://opensource.org/licenses/MIT
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

    public function getNamePrefix(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NPFX);
    }

    public function getGivenName(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_GIVN);
    }

    public function getNickName(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NICK);
    }

    public function getSurnamePrefix(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_SPFX);
    }

    public function getSurname(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_SURN);
    }

    public function getNameSuffix(): ?string
    {
        return $this->getValue(PersonalNamePiecesInterface::TAG_NSFX);
    }
}
