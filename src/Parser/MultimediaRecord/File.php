<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\MultimediaRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\MultimediaRecord\FileInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            FileInterface::TAG_FORM => MediaFormat::class,
            FileInterface::TAG_TITL => Common::class,
        ];
    }

    /**
     * Parses an OBJE-FILE block.
     *
     * @return FileModel
     */
    public function parse(): FileModel
    {
        $file = new FileModel();
        $file->setValue(FileInterface::TAG_FILE_REFN, $this->reader->value());

        $this->process($file);

        return $file;
    }
}
