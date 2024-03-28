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
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;
use MagicSunday\Gedcom\Interfaces\NoteRecordInterface;
use MagicSunday\Gedcom\Model\NoteRecord as NoteRecordModel;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\ReferenceNumber;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            NoteRecordInterface::TAG_REFN     => ReferenceNumber::class,
            NoteRecordInterface::TAG_RIN      => Common::class,
            SourceCitationInterface::TAG_SOUR => SourceCitation::class,
            ChangeDateInterface::TAG_CHAN     => ChangeDateStructure::class,
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
        $noteRecord->setValue(NoteRecordInterface::TAG_XREF_NOTE, $this->reader->identifier());

        $noteContent = $this->readContent();

        if ($noteContent) {
            $noteRecord->setValue(NoteRecordInterface::TAG_SUBMITTER_TEXT, $noteContent);
        }

        $this->process($noteRecord);

        return $noteRecord;
    }
}
