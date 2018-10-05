<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\SourceRecord\SourceRepositoryCitation;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\SourceRecord\SourceRepositoryCitation\SourceCallNumber as SourceCallNumberModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A CALN record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceCallNumber extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SourceCallNumberModel::TAG_MEDI => Common::class,
        ];
    }

    /**
     * Parses a CALN record block.
     *
     * @return SourceCallNumberModel
     */
    public function parse(): SourceCallNumberModel
    {
        $callNumber = new SourceCallNumberModel();
        $callNumber->setValue(SourceCallNumberModel::TAG_SOURCE_CALL_NUMBER, $this->reader->value());

        $this->process($callNumber);

        return $callNumber;
    }
}
