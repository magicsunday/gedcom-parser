<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header;

use MagicSunday\Gedcom\Model\DataObject;

/**
 * The gedcom information structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class GedcomInfo extends DataObject
{
    /**
     * An identifier that represents the version level assigned to the associated product. It is defined and
     * changed by the creators of the product.
     */
    const TAG_VERS = 'VERS';

    /**
     * The GEDCOM form used to construct this transmission. There maybe other forms used such as
     * CommSoft's "EVENT_LINEAGE_LINKED" but these specifications define only the LINEAGELINKED
     * Form. Systems will use this value to specify GEDCOM compatible with these specifications.
     */
    const TAG_FORM = 'FORM';

    /**
     * @return null|string
     */
    public function getVersion()
    {
        return $this->getValue(self::TAG_VERS);
    }

    /**
     * @return null|string
     */
    public function getForm()
    {
        return $this->getValue(self::TAG_FORM);
    }
}
