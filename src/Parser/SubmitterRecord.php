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
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\SubmitterRecordInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            SubmitterRecordInterface::TAG_NAME   => Common::class,
            AddressStructureInterface::TAG_ADDR  => AddressStructure::class,
            AddressStructureInterface::TAG_PHON  => Common::class,
            AddressStructureInterface::TAG_EMAIL => Common::class,
            AddressStructureInterface::TAG_FAX   => Common::class,
            AddressStructureInterface::TAG_WWW   => Common::class,
            MultimediaLinkInterface::TAG_OBJE    => MultimediaLink::class,
            SubmitterRecordInterface::TAG_LANG   => Common::class,
            SubmitterRecordInterface::TAG_RFN    => Common::class,
            SubmitterRecordInterface::TAG_RIN    => Common::class,
            NoteInterface::TAG_NOTE              => NoteStructure::class,
            ChangeDateInterface::TAG_CHAN        => ChangeDateStructure::class,
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
        $submitter->setValue(SubmitterRecordInterface::TAG_XREF_SUBM, $this->reader->identifier());

        $this->process($submitter);

        return $submitter;
    }
}
