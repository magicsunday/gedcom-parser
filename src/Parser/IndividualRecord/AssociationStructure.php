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
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\AssociationStructureInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            AssociationStructureInterface::TAG_RELA => Common::class,
            NoteInterface::TAG_NOTE                 => NoteStructure::class,
            SourceCitationInterface::TAG_SOUR       => SourceCitation::class,
        ];
    }

    /**
     * @return AssociationStructureModel
     */
    public function parse(): AssociationStructureModel
    {
        $childToFamilyLink = new AssociationStructureModel();
        $childToFamilyLink->setValue(AssociationStructureInterface::TAG_XREF_INDI, $this->reader->xref());

        $this->process($childToFamilyLink);

        return $childToFamilyLink;
    }
}
