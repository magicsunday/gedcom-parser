<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\AssociationStructure as AssociationStructureModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

/**
 * The ASSO (association) structure parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class AssociationStructure extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            AssociationStructureModel::TAG_RELA => Common::class,
            AssociationStructureModel::TAG_NOTE => NoteStructure::class,
            AssociationStructureModel::TAG_SOUR => SourceCitation::class,
        ];
    }

    /**
     *
     * @return AssociationStructureModel
     */
    public function parse(): AssociationStructureModel
    {
        $childToFamilyLink = new AssociationStructureModel();
        $childToFamilyLink->setValue(AssociationStructureModel::TAG_XREF_INDI, $this->reader->xref());

        $this->process($childToFamilyLink);

        return $childToFamilyLink;
    }
}
