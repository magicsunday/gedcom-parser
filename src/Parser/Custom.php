<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Custom as CustomModel;

/**
 * A custom GEDCOM tag parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Custom extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [];
    }

    /**
     * Parses a custom block.
     *
     * @return CustomModel
     */
    public function parse(): CustomModel
    {
        $data = new CustomModel();
        $data->setValue(
            'VALUE',
            $this->reader->xref() ?? $this->reader->value()
        );

        while ($this->reader->read() && $this->valid()) {
            $data->setValue($this->reader->tag(), $this->reader->value());
        }

        return $data;
    }
}
