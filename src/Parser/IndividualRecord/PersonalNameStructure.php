<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructureInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure as PersonalNameStructureModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\IndividualRecord\PersonalNameStructure\NamePhoneticVariation;
use MagicSunday\Gedcom\Parser\IndividualRecord\PersonalNameStructure\NameRomanizedVariation;
use MagicSunday\Gedcom\Parser\IndividualRecord\PersonalNameStructure\PersonalNamePieces;

/**
 * The personal name structure (PERSONAL_NAME_STRUCTURE) parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PersonalNameStructure extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return PersonalNamePieces::getClassMap()
            + [
                PersonalNameStructureInterface::TAG_TYPE => Common::class,
                PersonalNameStructureInterface::TAG_FONE => NamePhoneticVariation::class,
                PersonalNameStructureInterface::TAG_ROMN => NameRomanizedVariation::class,
            ];
    }

    /**
     * @return PersonalNameStructureModel
     */
    public function parse(): PersonalNameStructureModel
    {
        $personalNameStructure = new PersonalNameStructureModel();
        $personalNameStructure->setValue(PersonalNameStructureInterface::TAG_NAME_PERSONAL, $this->reader->value());

        $this->process($personalNameStructure);

        return $personalNameStructure;
    }
}
