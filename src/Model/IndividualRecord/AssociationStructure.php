<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\AssociationStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\Note;
use MagicSunday\Gedcom\Traits\Common\SourceCitation;

/**
 * The association structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class AssociationStructure extends DataObject implements AssociationStructureInterface
{
    use Note;
    use SourceCitation;

    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_INDI);
    }

    /**
     * @inheritDoc
     */
    public function getRelationShip()
    {
        return $this->getValue(self::TAG_RELA);
    }
}
