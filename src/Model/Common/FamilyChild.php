<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Interfaces\Common\FamilyChildInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The family child structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class FamilyChild extends DataObject implements FamilyChildInterface
{
    /**
     * {@inheritDoc}
     */
    public function getXref(): ?string
    {
        return $this->getValue(self::TAG_XREF_FAM);
    }

    /**
     * {@inheritDoc}
     */
    public function getAdoptedBy(): ?string
    {
        return $this->getValue(self::TAG_ADOP);
    }
}
