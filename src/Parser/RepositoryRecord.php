<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\RepositoryRecord as RepositoryRecordModel;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\ChangeDate\ChangeDateStructure;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\ReferenceNumber;

/**
 * A REPO record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class RepositoryRecord extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            RepositoryRecordModel::TAG_NAME  => Common::class,
            RepositoryRecordModel::TAG_ADDR  => AddressStructure::class,
            RepositoryRecordModel::TAG_PHON  => Common::class,
            RepositoryRecordModel::TAG_EMAIL => Common::class,
            RepositoryRecordModel::TAG_FAX   => Common::class,
            RepositoryRecordModel::TAG_WWW   => Common::class,
            RepositoryRecordModel::TAG_NOTE  => NoteStructure::class,
            RepositoryRecordModel::TAG_REFN  => ReferenceNumber::class,
            RepositoryRecordModel::TAG_RIN   => Common::class,
            RepositoryRecordModel::TAG_CHAN  => ChangeDateStructure::class,
        ];
    }

    /**
     * Parses a REPO record block.
     *
     * @return RepositoryRecordModel
     */
    public function parse(): RepositoryRecordModel
    {
        $repoRecord = new RepositoryRecordModel();
        $repoRecord->setValue(RepositoryRecordModel::TAG_XREF_REPO, $this->reader->identifier());

        $this->process($repoRecord);

        return $repoRecord;
    }
}
