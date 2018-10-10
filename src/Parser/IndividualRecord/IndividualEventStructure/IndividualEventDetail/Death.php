<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail\Death as DeathModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

/**
 * The individual DEAT (death) event detail parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Death extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            // Individual event details
            DeathModel::TAG_AGE   => Common::class,

            // Common event details
            DeathModel::TAG_TYPE  => Common::class,
            DeathModel::TAG_DATE  => Common::class,
            DeathModel::TAG_PLAC  => PlaceStructure::class,
            DeathModel::TAG_ADDR  => AddressStructure::class,
            DeathModel::TAG_PHON  => Common::class,
            DeathModel::TAG_EMAIL => Common::class,
            DeathModel::TAG_FAX   => Common::class,
            DeathModel::TAG_WWW   => Common::class,
            DeathModel::TAG_AGNC  => Common::class,
            DeathModel::TAG_RELI  => Common::class,
            DeathModel::TAG_CAUS  => Common::class,
            DeathModel::TAG_RESN  => Common::class,
            DeathModel::TAG_NOTE  => NoteStructure::class,
            DeathModel::TAG_SOUR  => SourceCitation::class,
            DeathModel::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     * Parse a DEAT block.
     *
     * @return DeathModel
     */
    public function parse(): DeathModel
    {
        $eventDetail = new DeathModel();
        $eventDetail->setValue(DeathModel::TAG_FLAG, $this->reader->value());

        $this->process($eventDetail);

        return $eventDetail;
    }
}
