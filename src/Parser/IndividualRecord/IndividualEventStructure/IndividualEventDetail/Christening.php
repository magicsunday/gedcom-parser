<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\IndividualRecord\IndividualEventStructure\IndividualEventDetail;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\IndividualRecord\IndividualEventStructure\IndividualEventDetail\Christening as ChristeningModel;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\AddressStructure;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\PlaceStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation;

/**
 * The individual CHR (christening) event detail parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Christening extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public function getClassMap(): array
    {
        return [
            // Christening event details
            ChristeningModel::TAG_FAMC => Common::class,

            // Individual event details
            ChristeningModel::TAG_AGE   => Common::class,

            // Common event details
            ChristeningModel::TAG_TYPE  => Common::class,
            ChristeningModel::TAG_DATE  => Common::class,
            ChristeningModel::TAG_PLAC  => PlaceStructure::class,
            ChristeningModel::TAG_ADDR  => AddressStructure::class,
            ChristeningModel::TAG_PHON  => Common::class,
            ChristeningModel::TAG_EMAIL => Common::class,
            ChristeningModel::TAG_FAX   => Common::class,
            ChristeningModel::TAG_WWW   => Common::class,
            ChristeningModel::TAG_AGNC  => Common::class,
            ChristeningModel::TAG_RELI  => Common::class,
            ChristeningModel::TAG_CAUS  => Common::class,
            ChristeningModel::TAG_RESN  => Common::class,
            ChristeningModel::TAG_NOTE  => NoteStructure::class,
            ChristeningModel::TAG_SOUR  => SourceCitation::class,
            ChristeningModel::TAG_OBJE  => MultimediaLink::class,
        ];
    }

    /**
     * Parse a CHR block.
     *
     * @return ChristeningModel
     */
    public function parse(): ChristeningModel
    {
        $eventDetail = new ChristeningModel();
        $eventDetail->setValue(ChristeningModel::TAG_FLAG, $this->reader->value());

        $this->process($eventDetail);

        return $eventDetail;
    }
}
