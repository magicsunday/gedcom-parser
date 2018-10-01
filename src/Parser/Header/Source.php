<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Header;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\Source as SourceModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Header\Source\Corporation;
use MagicSunday\Gedcom\Parser\Header\Source\Data;

/**
 * A SOUR parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Source extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
            SourceModel::TAG_VERS => Common::class,
            SourceModel::TAG_NAME => Common::class,
            SourceModel::TAG_CORP => Corporation::class,
            SourceModel::TAG_DATA => Data::class,
        ];
    }

    /**
     * Parses a SOUR block.
     *
     * @return SourceModel
     */
    public function parse(): SourceModel
    {
        $source = new SourceModel();
        $source->setValue(SourceModel::TAG_APPROVED_SYSTEM_ID, $this->reader->value());

        $this->process($source);

        return $source;
    }
}
