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
use MagicSunday\Gedcom\Interfaces\IndividualRecord\SpouseToFamilyLinkInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\SpouseToFamilyLink as SpouseToFamilyLinkModel;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;

/**
 * The spouse to family (SPOUSE_TO_FAMILY_LINK) parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SpouseToFamilyLink extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            NoteInterface::TAG_NOTE => NoteStructure::class,
        ];
    }

    /**
     * @return SpouseToFamilyLinkModel
     */
    public function parse(): SpouseToFamilyLinkModel
    {
        $childToFamilyLink = new SpouseToFamilyLinkModel();
        $childToFamilyLink->setValue(SpouseToFamilyLinkInterface::TAG_XREF_FAM, $this->reader->xref());

        $this->process($childToFamilyLink);

        return $childToFamilyLink;
    }
}
