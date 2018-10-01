<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Individual\Name;

use MagicSunday\Gedcom\Interfaces\Individual\Name\PersonalNamePiecesInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Individual\Name\PersonalNamePieces as PersonalNamePiecesTrait;
use MagicSunday\Gedcom\Traits\NoteStructure;
use MagicSunday\Gedcom\Traits\SourceCitation;

/**
 * The personal name pieces model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PersonalNamePieces extends DataObject implements PersonalNamePiecesInterface
{
    use PersonalNamePiecesTrait;
    use SourceCitation;
    use NoteStructure;
}
