<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\HeaderRecord;

/**
 * The GEDCOM information structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface GedcomInfoInterface
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
     * @return string
     */
    public function getVersion(): string;

    /**
     * @return string
     */
    public function getForm(): string;
}
