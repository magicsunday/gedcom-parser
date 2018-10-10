<?php
declare(strict_types=1);
/**
 * See LICENSE.md file for further details.
 */
namespace MagicSunday\Gedcom\Parser\IndividualRecord\Name;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\Name\NamePhoneticVariation as PhoneticName;
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
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return PersonalNamePieces::getClassMap()
            + [
                PhoneticName::TAG_TYPE => Common::class,
            ];
    }

    /**
     *
     * @return PhoneticName
     */
    public function parse(): PhoneticName
    {
        $phonetic = new PhoneticName();
        $phonetic->setValue(PhoneticName::TAG_NAME_PHONETIC_VARIATION, $this->reader->value());

        $this->process($phonetic);

        return $phonetic;
    }
}
