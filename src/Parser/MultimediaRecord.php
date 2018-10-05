<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
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
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            MultimediaRecordModel::TAG_FILE => File::class,
            MultimediaRecordModel::TAG_REFN => ReferenceNumber::class,
            MultimediaRecordModel::TAG_RIN  => Common::class,
            MultimediaRecordModel::TAG_NOTE => NoteStructure::class,
            MultimediaRecordModel::TAG_SOUR => SourceCitation::class,
            MultimediaRecordModel::TAG_CHAN => ChangeDateStructure::class,
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
        $mediaRecord->setValue(MultimediaRecordModel::TAG_XREF_OBJE, $this->reader->identifier());

        $this->process($mediaRecord);

        return $mediaRecord;
    }
}
