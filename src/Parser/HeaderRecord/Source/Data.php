<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\HeaderRecord\Source;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\DataInterface;
use MagicSunday\Gedcom\Model\HeaderRecord\Source\Data as DataModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\DateExact;

/**
 * A DATA parser.
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
            DataInterface::TAG_DATE => DateExact::class,

            // TODO Handle CONT|CONC
            DataInterface::TAG_COPR => Common::class,
        ];
    }

    /**
     * Parses a DATA block.
     *
     * @return DataModel
     */
    public function parse(): DataModel
    {
        $data = new DataModel();
        $data->setValue(DataInterface::TAG_NAME_OF_SOURCE_DATA, $this->reader->value());

        $this->process($data);

        return $data;
    }
}
