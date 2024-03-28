<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\FamilyChildInterface;
use MagicSunday\Gedcom\Model\Common\FamilyChild as FamilyChildModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A FAMC parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyChild extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            FamilyChildInterface::TAG_ADOP => Common::class,
        ];
    }

    /**
     * Parses a FAMC block.
     *
     * @return FamilyChildModel
     */
    public function parse(): FamilyChildModel
    {
        $familyChild = new FamilyChildModel();
        $familyChild->setValue(FamilyChildInterface::TAG_XREF_FAM, $this->reader->xref());

        $this->process($familyChild);

        return $familyChild;
    }
}
