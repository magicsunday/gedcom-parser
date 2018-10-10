<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\PersonalNamePiecesInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\Note;
use MagicSunday\Gedcom\Traits\Common\SourceCitation;
use MagicSunday\Gedcom\Traits\IndividualRecord\PersonalNameStructure\PersonalNamePieces as PersonalNamePiecesTrait;

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
    use Note;
}
