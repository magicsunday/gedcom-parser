<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\MultimediaRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\MultimediaRecord\File as FileModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\MultimediaRecord\File\MediaFormat;

/**
 * The OBJE-FILE parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class File extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            FileModel::TAG_FORM => MediaFormat::class,
            FileModel::TAG_TITL => Common::class,
        ];
    }

    /**
     * Parses a OBJE-FILE block.
     *
     * @return FileModel
     */
    public function parse(): FileModel
    {
        $file = new FileModel();
        $file->setValue(FileModel::TAG_FILE_REFN, $this->reader->value());

        $this->process($file);

        return $file;
    }
}
