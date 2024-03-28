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
use MagicSunday\Gedcom\Interfaces\Common\AddressStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\ChangeDateInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\RepositoryRecordInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            RepositoryRecordInterface::TAG_NAME  => Common::class,
            AddressStructureInterface::TAG_ADDR  => AddressStructure::class,
            AddressStructureInterface::TAG_PHON  => Common::class,
            AddressStructureInterface::TAG_EMAIL => Common::class,
            AddressStructureInterface::TAG_FAX   => Common::class,
            AddressStructureInterface::TAG_WWW   => Common::class,
            NoteInterface::TAG_NOTE              => NoteStructure::class,
            RepositoryRecordInterface::TAG_REFN  => ReferenceNumber::class,
            RepositoryRecordInterface::TAG_RIN   => Common::class,
            ChangeDateInterface::TAG_CHAN        => ChangeDateStructure::class,
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
        $repoRecord->setValue(RepositoryRecordInterface::TAG_XREF_REPO, $this->reader->identifier());

        $this->process($repoRecord);

        return $repoRecord;
    }
}
