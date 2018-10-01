<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\MultimediaLink\File;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\MultimediaLink\File\Format as FormatModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * The OBJE-FILE-FORM parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Format extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            FormatModel::TAG_MEDI => Common::class,
        ];
    }

    /**
     * Parses a OBJE-FILE-FORM block.
     *
     * @return FormatModel
     */
    public function parse(): FormatModel
    {
        $format = new FormatModel();
        $format->setValue(FormatModel::TAG_TYPE, $this->reader->value());

        $this->process($format);

        return $format;
    }
}
