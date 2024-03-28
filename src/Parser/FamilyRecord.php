<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecord\FamilyEventStructureInterface;
use MagicSunday\Gedcom\Interfaces\FamilyRecordInterface;
use MagicSunday\Gedcom\Model\FamilyRecord as FamilyRecordModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\ReferenceNumber;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure\FamilyEventDetail;
use MagicSunday\Gedcom\Parser\FamilyRecord\FamilyEventStructure\FamilyEventDetail\Marriage;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            FamilyRecordInterface::TAG_RESN   => Common::class,
            FamilyRecordInterface::TAG_HUSB   => Common::class,
            FamilyRecordInterface::TAG_WIFE   => Common::class,
            FamilyRecordInterface::TAG_CHIL   => Common::class,
            FamilyRecordInterface::TAG_NCHI   => Common::class,
            FamilyRecordInterface::TAG_SUBM   => Common::class,
            FamilyRecordInterface::TAG_SLGS   => LdsSpouseSealing::class,
            FamilyRecordInterface::TAG_REFN   => ReferenceNumber::class,
            FamilyRecordInterface::TAG_RIN    => Common::class,
            ChangeDateInterface::TAG_CHAN     => ChangeDateStructure::class,
            MultimediaLinkInterface::TAG_OBJE => MultimediaLink::class,
            NoteInterface::TAG_NOTE           => NoteStructure::class,
            SourceCitationInterface::TAG_SOUR => SourceCitation::class,

            // Family events
            FamilyEventStructureInterface::TAG_ANUL => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_CENS => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_DIV  => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_DIVF => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_ENGA => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_MARB => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_MARC => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_MARR => Marriage::class,
            FamilyEventStructureInterface::TAG_MARL => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_MARS => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_RESI => FamilyEventDetail::class,
            FamilyEventStructureInterface::TAG_EVEN => FamilyEventDetail::class,
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
        $family->setValue(FamilyRecordInterface::TAG_XREF_FAM, $this->reader->identifier());

        $this->process($family);

        return $family;
    }
}
