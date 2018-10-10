<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure\FamilyPersonAge as FamilyPersonAgeModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A FAM (family) event AGE structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyPersonAge extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            FamilyPersonAgeModel::TAG_AGE => Common::class,
        ];
    }

    /**
     * Parse a AGE block.
     *
     * @return FamilyPersonAgeModel
     */
    public function parse(): FamilyPersonAgeModel
    {
        $age = new FamilyPersonAgeModel();

        $this->process($age);

        return $age;
    }
}
