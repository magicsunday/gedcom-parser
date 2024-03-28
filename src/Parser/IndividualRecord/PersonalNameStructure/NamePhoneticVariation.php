<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\PersonalNameStructure;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructure\NamePhoneticVariationInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure\NamePhoneticVariation as PhoneticName;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A individual name parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NamePhoneticVariation extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return PersonalNamePieces::getClassMap()
            + [
                NamePhoneticVariationInterface::TAG_TYPE => Common::class,
            ];
    }

    /**
     * @return PhoneticName
     */
    public function parse(): PhoneticName
    {
        $phonetic = new PhoneticName();
        $phonetic->setValue(NamePhoneticVariationInterface::TAG_NAME_PHONETIC_VARIATION, $this->reader->value());

        $this->process($phonetic);

        return $phonetic;
    }
}
