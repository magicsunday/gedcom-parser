<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;

/**
 * The association structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface AssociationStructureInterface extends
    NoteInterface,
    SourceCitationInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a individual record.
     */
    const TAG_XREF_INDI = 'XREF:INDI';

    /**
     * A relationship value between the indicated contexts.
     */
    const TAG_RELA = 'RELA';

    /**
     * @return string
     */
    public function getXref(): string;

    /**
     * @return string
     */
    public function getRelationShip(): string;
}
