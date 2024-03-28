<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
interface AssociationStructureInterface extends NoteInterface, SourceCitationInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, an individual record.
     */
    public const TAG_XREF_INDI = 'XREF:INDI';

    /**
     * A relationship value between the indicated contexts.
     */
    public const TAG_RELA = 'RELA';

    /**
     * @return string
     */
    public function getXref(): string;

    /**
     * @return string
     */
    public function getRelationShip(): string;
}
