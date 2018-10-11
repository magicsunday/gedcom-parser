<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\SpouseToFamilyLinkInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\Note;

/**
 * The spouse to family link tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SpouseToFamilyLink extends DataObject implements SpouseToFamilyLinkInterface
{
    use Note;

    /**
     * @return null|string
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_FAM);
    }
}
