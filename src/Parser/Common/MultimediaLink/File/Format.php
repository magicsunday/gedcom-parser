<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\MultimediaLink\File;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\File\FormatInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            FormatInterface::TAG_MEDI => Common::class,
        ];
    }

    /**
     * Parses an OBJE-FILE-FORM block.
     *
     * @return FormatModel
     */
    public function parse(): FormatModel
    {
        $format = new FormatModel();
        $format->setValue(FormatInterface::TAG_TYPE, $this->reader->value());

        $this->process($format);

        return $format;
    }
}
