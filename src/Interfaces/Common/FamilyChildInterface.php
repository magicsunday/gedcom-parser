<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

/**
 * The FAMC (family child) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface FamilyChildInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a family record.
     */
    const TAG_XREF_FAM = 'XREF:FAM';

    /**
     * A code which shows which parent in the associated family record adopted this person.
     *
     * HUSB, WIFE, BOTH
     */
    const TAG_ADOP = 'ADOP';

    /**
     * @return null|string
     */
    public function getXref();

    /**
     * @return null|string
     */
    public function getAdoptedBy();
}
