<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;

/**
 * The child to family link tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface ChildToFamilyLinkInterface extends NoteInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    const TAG_XREF_FAM = 'XREF:FAM';

    /**
     * A code used to indicate the child to family relationship for pedigree navigation purposes.
     *
     * adopted = indicates adoptive parents
     * birth   = indicates birth parents
     * foster  = indicates child was included in a foster or guardian family
     * sealing = indicates child was sealed to parents other than birth parents
     */
    const TAG_PEDI = 'PEDI';

    /**
     * A status code that allows passing on the users opinion of the status of a child to family link.
     *
     * challenged = Linking this child to this family is suspect, but the linkage has been neither proven nor disproven
     * disproven  = There has been a claim by some that this child belongs to this family, but the linkage has been disproven
     * proven     = There has been a claim by some that this child does not belongs to this family, but the linkage has been proven
     */
    const TAG_STAT = 'STAT';

    /**
     * @return string
     */
    public function getXref(): string;

    /**
     * @return null|string
     */
    public function getPedigreeLinkageType();

    /**
     * @return null|string
     */
    public function getChildLinkageStatus();
}
