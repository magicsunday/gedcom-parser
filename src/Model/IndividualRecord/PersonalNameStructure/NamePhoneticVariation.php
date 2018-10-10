<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\NamePhoneticVariationInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Individual\PersonalNameStructure\PersonalNamePieces;
use MagicSunday\Gedcom\Traits\Common\Note;
use MagicSunday\Gedcom\Traits\Common\SourceCitation;

/**
 * The name phonetic variation model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NamePhoneticVariation extends DataObject implements NamePhoneticVariationInterface
{
    use Note;
    use PersonalNamePieces;
    use SourceCitation;

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
