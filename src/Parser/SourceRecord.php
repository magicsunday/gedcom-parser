<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\SourceRecord as SourceRecordModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\ReferenceNumber;
use MagicSunday\Gedcom\Parser\Common\SourceCitation\Text;
use MagicSunday\Gedcom\Parser\SourceRecord\Data;
use MagicSunday\Gedcom\Parser\SourceRecord\SourceRepositoryCitation;

/**
 * A SOUR (source) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceRecord extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SourceRecordModel::TAG_DATA => Data::class,
            SourceRecordModel::TAG_AUTH => Text::class,
            SourceRecordModel::TAG_TITL => Text::class,
            SourceRecordModel::TAG_ABBR => Common::class,
            SourceRecordModel::TAG_PUBL => Text::class,
            SourceRecordModel::TAG_TEXT => Text::class,
            SourceRecordModel::TAG_REPO => SourceRepositoryCitation::class,
            SourceRecordModel::TAG_REFN => ReferenceNumber::class,
            SourceRecordModel::TAG_RIN  => Common::class,
            SourceRecordModel::TAG_CHAN => ChangeDateStructure::class,
            SourceRecordModel::TAG_OBJE => MultimediaLink::class,
            SourceRecordModel::TAG_NOTE => NoteStructure::class,
        ];
    }

    /**
     * Parses a SOUR record block.
     *
     * @return SourceRecordModel
     */
    public function parse(): SourceRecordModel
    {
        $sourceRecord = new SourceRecordModel();
        $sourceRecord->setValue(SourceRecordModel::TAG_XREF_SOUR, $this->reader->identifier());

        $this->process($sourceRecord);

        return $sourceRecord;
    }
}
