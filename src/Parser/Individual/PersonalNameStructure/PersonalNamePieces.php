<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Individual\PersonalNameStructure;

use MagicSunday\Gedcom\Model\Individual\PersonalNameStructure\PersonalNamePieces as PersonalNamePiecesModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\NoteStructure;
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
     * {@inheritdoc}
     */
    public static function getClassMap(): array
    {
        return [
            PersonalNamePiecesModel::TAG_NPFX => Common::class,
            PersonalNamePiecesModel::TAG_GIVN => Common::class,
            PersonalNamePiecesModel::TAG_NICK => Common::class,
            PersonalNamePiecesModel::TAG_SPFX => Common::class,
            PersonalNamePiecesModel::TAG_SURN => Common::class,
            PersonalNamePiecesModel::TAG_NSFX => Common::class,
            PersonalNamePiecesModel::TAG_NOTE => NoteStructure::class,
//            PersonalNamePiecesModel::TAG_SOUR => SourceCitation::class,
        ];
    }
}
