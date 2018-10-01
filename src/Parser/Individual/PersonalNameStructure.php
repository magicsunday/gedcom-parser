<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Individual;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Individual\PersonalNameStructure as PersonalNameStructureModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Individual\PersonalNameStructure\PersonalNamePieces;
use MagicSunday\Gedcom\Parser\Individual\PersonalNameStructure\Phonetic;
use MagicSunday\Gedcom\Parser\Individual\PersonalNameStructure\Romanized;

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
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return PersonalNamePieces::getClassMap()
           + [
                PersonalNameStructureModel::TAG_TYPE => Common::class,
                PersonalNameStructureModel::TAG_FONE => Phonetic::class,
                PersonalNameStructureModel::TAG_ROMN => Romanized::class,
            ];
    }

    /**
     *
     * @return PersonalNameStructureModel
     */
    public function parse(): PersonalNameStructureModel
    {
        $personalNameStructure = new PersonalNameStructureModel();
        $personalNameStructure->setValue(PersonalNameStructureModel::TAG_NAME_PERSONAL, $this->reader->value());

        $this->process($personalNameStructure);

        return $personalNameStructure;
    }
}
