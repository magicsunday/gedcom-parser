<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\HeaderRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\CharacterSetInterface;
use MagicSunday\Gedcom\Model\HeaderRecord\CharacterSet as CharacterSetModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A CHAR parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class CharacterSet extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            CharacterSetInterface::TAG_VERS => Common::class,
        ];
    }

    /**
     * Parses a CHAR block.
     *
     * @return CharacterSetModel
     */
    public function parse(): CharacterSetModel
    {
        $characterSet = new CharacterSetModel();
        $characterSet->setValue(CharacterSetInterface::TAG_CHARACTER_SET, $this->reader->value());

        $this->process($characterSet);

        return $characterSet;
    }
}
