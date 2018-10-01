<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Header;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Header\GedcomInfo as GedcomInfoModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A GEDC parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class GedcomInfo extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            GedcomInfoModel::TAG_VERS => Common::class,
            GedcomInfoModel::TAG_FORM => Common::class,
        ];
    }

    /**
     * Parses a GEDC block.
     *
     * @return GedcomInfoModel
     */
    public function parse(): GedcomInfoModel
    {
        $gedcomInfo = new GedcomInfoModel();

        $this->process($gedcomInfo);

        return $gedcomInfo;
    }
}
