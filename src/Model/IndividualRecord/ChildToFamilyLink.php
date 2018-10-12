<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\ChildToFamilyLinkInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The child to family link structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ChildToFamilyLink extends DataObject implements ChildToFamilyLinkInterface
{
    use NoteTrait;

    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_FAM);
    }

    /**
     * @inheritDoc
     */
    public function getPedigreeLinkageType()
    {
        return $this->getValue(self::TAG_PEDI);
    }

    /**
     * @inheritDoc
     */
    public function getChildLinkageStatus()
    {
        return $this->getValue(self::TAG_STAT);
    }
}
