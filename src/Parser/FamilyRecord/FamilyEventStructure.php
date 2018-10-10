<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\FamilyRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\FamilyRecord\FamilyEventStructure as FamilyEventStructureModel;
use MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure\FamilyEventDetail;

/**
 * A FAM (family) event structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyEventStructure extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            FamilyEventStructureModel::TAG_ANUL => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_CENS => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_DIV  => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_DIVF => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_ENGA => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_MARB => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_MARC => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_MARR => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_MARL => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_MARS => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_RESI => FamilyEventDetail::class,
            FamilyEventStructureModel::TAG_EVEN => FamilyEventDetail::class,
        ];
    }

    /**
     * Parse a event block.
     *
     * @return FamilyEventStructureModel
     */
    public function parse(): FamilyEventStructureModel
    {
        $eventStructure = new FamilyEventStructureModel();

        $this->process($eventStructure);

        return $eventStructure;
    }
}
