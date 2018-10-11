<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\ChildToFamilyLink as ChildToFamilyLinkModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;

/**
 * The child to family (CHILD_TO_FAMILY_LINK) parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ChildToFamilyLink extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            ChildToFamilyLinkModel::TAG_PEDI => Common::class,
            ChildToFamilyLinkModel::TAG_STAT => Common::class,
            ChildToFamilyLinkModel::TAG_NOTE => NoteStructure::class,
        ];
    }

    /**
     *
     * @return ChildToFamilyLinkModel
     */
    public function parse(): ChildToFamilyLinkModel
    {
        $childToFamilyLink = new ChildToFamilyLinkModel();
        $childToFamilyLink->setValue(ChildToFamilyLinkModel::TAG_XREF_FAM, $this->reader->xref());

        $this->process($childToFamilyLink);

        return $childToFamilyLink;
    }
}
