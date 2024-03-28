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
use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\MultimediaRecordInterface;
use MagicSunday\Gedcom\Model\MultimediaRecord as MultimediaRecordModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\ReferenceNumber;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Parser\MultimediaRecord\File;

/**
 * A OBJE (multimedia object) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class MultimediaRecord extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            MultimediaRecordInterface::TAG_FILE => File::class,
            MultimediaRecordInterface::TAG_REFN => ReferenceNumber::class,
            MultimediaRecordInterface::TAG_RIN  => Common::class,
            NoteInterface::TAG_NOTE             => NoteStructure::class,
            SourceCitationInterface::TAG_SOUR   => SourceCitation::class,
            ChangeDateInterface::TAG_CHAN       => ChangeDateStructure::class,
        ];
    }

    /**
     * Parses a NOTE record block.
     *
     * @return MultimediaRecordModel
     */
    public function parse(): MultimediaRecordModel
    {
        $mediaRecord = new MultimediaRecordModel();
        $mediaRecord->setValue(MultimediaRecordInterface::TAG_XREF_OBJE, $this->reader->identifier());

        $this->process($mediaRecord);

        return $mediaRecord;
    }
}
