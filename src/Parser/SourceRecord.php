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
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\SourceRecordInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            SourceRecordInterface::TAG_DATA   => Data::class,
            SourceRecordInterface::TAG_AUTH   => Text::class,
            SourceRecordInterface::TAG_TITL   => Text::class,
            SourceRecordInterface::TAG_ABBR   => Common::class,
            SourceRecordInterface::TAG_PUBL   => Text::class,
            SourceRecordInterface::TAG_TEXT   => Text::class,
            SourceRecordInterface::TAG_REPO   => SourceRepositoryCitation::class,
            SourceRecordInterface::TAG_REFN   => ReferenceNumber::class,
            SourceRecordInterface::TAG_RIN    => Common::class,
            ChangeDateInterface::TAG_CHAN     => ChangeDateStructure::class,
            MultimediaLinkInterface::TAG_OBJE => MultimediaLink::class,
            NoteInterface::TAG_NOTE           => NoteStructure::class,
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
        $sourceRecord->setValue(SourceRecordInterface::TAG_XREF_SOUR, $this->reader->identifier());

        $this->process($sourceRecord);

        return $sourceRecord;
    }
}
