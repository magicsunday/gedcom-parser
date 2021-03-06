<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\SourceCitation;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Model\Common\SourceCitation\Data as DataModel;

/**
 * The SOUR-DATA parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Data extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            DataModel::TAG_DATE => Common::class,
            DataModel::TAG_TEXT => Text::class,
        ];
    }

    /**
     * Parses a SOUR-DATA block.
     *
     * @return DataModel
     */
    public function parse(): DataModel
    {
        $data = new DataModel();

        $this->process($data);

        return $data;
    }
}
