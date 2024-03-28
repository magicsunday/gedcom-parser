<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\AssociationStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;

/**
 * The association structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class AssociationStructure extends DataObject implements AssociationStructureInterface
{
    use NoteTrait;
    use SourceCitationTrait;

    /**
     * {@inheritDoc}
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_INDI);
    }

    /**
     * {@inheritDoc}
     */
    public function getRelationShip(): string
    {
        return $this->getValue(self::TAG_RELA);
    }
}
