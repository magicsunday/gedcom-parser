<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Individual\Name;

use MagicSunday\Gedcom\Interfaces\Individual\Name\NamePhoneticVariationInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Individual\Name\PersonalNamePieces;
use MagicSunday\Gedcom\Traits\NoteStructure;
use MagicSunday\Gedcom\Traits\SourceCitation;

/**
 * The name phonetic variation model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NamePhoneticVariation extends DataObject implements NamePhoneticVariationInterface
{
    use PersonalNamePieces;
    use SourceCitation;
    use NoteStructure;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getValue(self::TAG_NAME_PHONETIC_VARIATION);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }
}
