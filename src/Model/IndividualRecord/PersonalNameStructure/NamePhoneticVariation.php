<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\NamePhoneticVariationInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;
use MagicSunday\Gedcom\Traits\IndividualRecord\PersonalNameStructure\PersonalNamePiecesTrait;

/**
 * The name phonetic variation model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NamePhoneticVariation extends DataObject implements NamePhoneticVariationInterface
{
    use NoteTrait;
    use PersonalNamePiecesTrait;
    use SourceCitationTrait;

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->getValue(self::TAG_NAME_PHONETIC_VARIATION);
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->getValue(self::TAG_TYPE);
    }
}
