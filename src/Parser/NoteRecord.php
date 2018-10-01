<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\NoteRecord as NoteRecordModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;
use MagicSunday\Gedcom\Parser\NoteRecord\ReferenceNumber;

/**
 * A NOTE record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NoteRecord extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            NoteRecordModel::TAG_REFN => ReferenceNumber::class,
            NoteRecordModel::TAG_RIN  => Common::class,
            NoteRecordModel::TAG_SOUR => SourceCitation::class,
            NoteRecordModel::TAG_CHAN => ChangeDateStructure::class,
        ];
    }

    /**
     * Parses a NOTE record block.
     *
     * @return NoteRecordModel
     */
    public function parse(): NoteRecordModel
    {
        $noteRecord = new NoteRecordModel();
        $noteRecord->setValue(NoteRecordModel::TAG_XREF_NOTE, $this->reader->identifier());

        $noteContent = $this->readContent();

        if ($noteContent) {
            $noteRecord->setValue(NoteRecordModel::TAG_SUBMITTER_TEXT, $noteContent);
        }

        $this->process($noteRecord);

        return $noteRecord;
    }
}
