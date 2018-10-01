<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Individual;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Individual\PersonalNameStructureInterface;
use MagicSunday\Gedcom\Model\Individual\PersonalNameStructure as PersonalNameStructureModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Individual\Name\NamePhoneticVariation;
use MagicSunday\Gedcom\Parser\Individual\Name\NameRomanizedVariation;
use MagicSunday\Gedcom\Parser\Individual\Name\PersonalNamePieces;

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
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return PersonalNamePieces::getClassMap()
            + [
                PersonalNameStructureModel::TAG_TYPE => Common::class,
                PersonalNameStructureModel::TAG_FONE => NamePhoneticVariation::class,
                PersonalNameStructureModel::TAG_ROMN => NameRomanizedVariation::class,
            ];
    }

    /**
     *
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
