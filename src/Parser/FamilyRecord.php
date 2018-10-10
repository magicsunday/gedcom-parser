<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\FamilyRecord as FamilyRecordModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\ReferenceNumber;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Parser\FamilyRecord\LdsSpouseSealing;

/**
 * A FAM (family) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyRecord extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            FamilyRecordModel::TAG_RESN => Common::class,
            FamilyRecordModel::TAG_HUSB => Common::class,
            FamilyRecordModel::TAG_WIFE => Common::class,
            FamilyRecordModel::TAG_CHIL => Common::class,
            FamilyRecordModel::TAG_NCHI => Common::class,
            FamilyRecordModel::TAG_SUBM => Common::class,
            FamilyRecordModel::TAG_SLGS => LdsSpouseSealing::class,
            FamilyRecordModel::TAG_REFN => ReferenceNumber::class,
            FamilyRecordModel::TAG_RIN  => Common::class,
            FamilyRecordModel::TAG_CHAN => ChangeDateStructure::class,
            FamilyRecordModel::TAG_OBJE => MultimediaLink::class,
            FamilyRecordModel::TAG_NOTE => NoteStructure::class,
            FamilyRecordModel::TAG_SOUR => SourceCitation::class,

            // TODO FamilyEvents
        ];
    }

    /**
     * Parse a FAM block.
     *
     * @return FamilyRecordModel
     */
    public function parse(): FamilyRecordModel
    {
        $family = new FamilyRecordModel();
        $family->setValue(FamilyRecordModel::TAG_XREF_FAM, $this->reader->identifier());

        $this->process($family);

        return $family;
    }
}