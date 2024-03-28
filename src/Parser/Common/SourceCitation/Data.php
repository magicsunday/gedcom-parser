<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\SourceCitation;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitation\DataInterface;
use MagicSunday\Gedcom\Model\Common\SourceCitation\Data as DataModel;
use MagicSunday\Gedcom\Parser\Common;

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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            DataInterface::TAG_DATE => Common::class,
            DataInterface::TAG_TEXT => Text::class,
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
