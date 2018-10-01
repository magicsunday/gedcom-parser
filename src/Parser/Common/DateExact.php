<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
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
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            DateExactModel::TAG_TIME => Common::class,
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
        $dateExact->setValue(DateExactModel::TAG_DATE_EXACT, $this->reader->value());

        $this->process($dateExact);

        return $dateExact;
    }
}
