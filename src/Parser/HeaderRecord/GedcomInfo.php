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
use MagicSunday\Gedcom\Interfaces\HeaderRecord\GedcomInfoInterface;
use MagicSunday\Gedcom\Model\HeaderRecord\GedcomInfo as GedcomInfoModel;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            GedcomInfoInterface::TAG_VERS => Common::class,
            GedcomInfoInterface::TAG_FORM => Common::class,
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
