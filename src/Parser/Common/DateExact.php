<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\DateExactInterface;
use MagicSunday\Gedcom\Model\Common\DateExact as DateExactModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * A DATE_EXACT parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class DateExact extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            DateExactInterface::TAG_TIME => Common::class,
        ];
    }

    /**
     * Parses a DATE_EXACT block.
     *
     * @return DateExactModel
     */
    public function parse(): DateExactModel
    {
        $dateExact = new DateExactModel();
        $dateExact->setValue(DateExactInterface::TAG_DATE_EXACT, $this->reader->value());

        $this->process($dateExact);

        return $dateExact;
    }
}
