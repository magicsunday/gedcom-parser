<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\MultimediaRecord\File;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\MultimediaRecord\File\MediaFormat as MediaFormatModel;
use MagicSunday\Gedcom\Parser\Common;

/**
 * The OBJE-FILE-FORM parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class MediaFormat extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            MediaFormatModel::TAG_TYPE => Common::class,
        ];
    }

    /**
     * Parses a OBJE-FILE-FORM block.
     *
     * @return MediaFormatModel
     */
    public function parse(): MediaFormatModel
    {
        $format = new MediaFormatModel();
        $format->setValue(MediaFormatModel::TAG_MULTIMEDIA_FORMAT, $this->reader->value());

        $this->process($format);

        return $format;
    }
}
