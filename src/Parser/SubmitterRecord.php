<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\SubmitterRecord as SubmitterRecordModel;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;

/**
 * A SUBM (submitter) record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SubmitterRecord extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SubmitterRecordModel::TAG_NAME  => Common::class,
            SubmitterRecordModel::TAG_ADDR  => AddressStructure::class,
            SubmitterRecordModel::TAG_PHON  => Common::class,
            SubmitterRecordModel::TAG_EMAIL => Common::class,
            SubmitterRecordModel::TAG_FAX   => Common::class,
            SubmitterRecordModel::TAG_WWW   => Common::class,
            SubmitterRecordModel::TAG_OBJE  => MultimediaLink::class,
            SubmitterRecordModel::TAG_LANG  => Common::class,
            SubmitterRecordModel::TAG_RFN   => Common::class,
            SubmitterRecordModel::TAG_RIN   => Common::class,
            SubmitterRecordModel::TAG_NOTE  => NoteStructure::class,
            SubmitterRecordModel::TAG_CHAN  => ChangeDateStructure::class,
        ];
    }

    /**
     * Parses a SUBM block.
     *
     * @return SubmitterRecordModel
     */
    public function parse(): SubmitterRecordModel
    {
        $submitter = new SubmitterRecordModel();
        $submitter->setValue(SubmitterRecordModel::TAG_XREF_SUBM, $this->reader->identifier());

        $this->process($submitter);

        return $submitter;
    }
}
