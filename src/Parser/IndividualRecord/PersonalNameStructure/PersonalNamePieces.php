<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\PersonalNamePiecesInterface;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

/**
 * The mapping for a the individual name pieces.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PersonalNamePieces
{
    /**
     * @inheritDoc
     */
    public static function getClassMap(): array
    {
        return [
            PersonalNamePiecesInterface::TAG_NPFX => Common::class,
            PersonalNamePiecesInterface::TAG_GIVN => Common::class,
            PersonalNamePiecesInterface::TAG_NICK => Common::class,
            PersonalNamePiecesInterface::TAG_SPFX => Common::class,
            PersonalNamePiecesInterface::TAG_SURN => Common::class,
            PersonalNamePiecesInterface::TAG_NSFX => Common::class,
            PersonalNamePiecesInterface::TAG_NOTE => NoteStructure::class,
            PersonalNamePiecesInterface::TAG_SOUR => SourceCitation::class,
        ];
    }
}
