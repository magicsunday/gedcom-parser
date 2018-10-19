<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\NameRomanizedVariationInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;
use MagicSunday\Gedcom\Traits\IndividualRecord\PersonalNameStructure\PersonalNamePiecesTrait;

/**
 * The name romanized variation model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NameRomanizedVariation extends DataObject implements NameRomanizedVariationInterface
{
    use NoteTrait;
    use PersonalNamePiecesTrait;
    use SourceCitationTrait;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getValue(self::TAG_NAME_ROMANIZED_VARIATION);
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->getValue(self::TAG_TYPE);
    }
}
