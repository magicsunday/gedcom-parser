<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Submitter as SubmitterModel;
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
class Submitter extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SubmitterModel::TAG_NAME  => Common::class,
            SubmitterModel::TAG_ADDR  => AddressStructure::class,
            SubmitterModel::TAG_PHON  => Common::class,
            SubmitterModel::TAG_EMAIL => Common::class,
            SubmitterModel::TAG_FAX   => Common::class,
            SubmitterModel::TAG_WWW   => Common::class,
            SubmitterModel::TAG_OBJE  => MultimediaLink::class,
            SubmitterModel::TAG_LANG  => Common::class,
            SubmitterModel::TAG_RFN   => Common::class,
            SubmitterModel::TAG_RIN   => Common::class,
            SubmitterModel::TAG_NOTE  => NoteStructure::class,
            SubmitterModel::TAG_CHAN  => ChangeDateStructure::class,
        ];
    }

    /**
     * Parses a SUBM block.
     *
     * @return SubmitterModel
     */
    public function parse(): SubmitterModel
    {
        $submitter = new SubmitterModel();
        $submitter->setValue(SubmitterModel::TAG_XREF_SUBM, $this->reader->identifier());

        $this->process($submitter);

        return $submitter;
    }
}
