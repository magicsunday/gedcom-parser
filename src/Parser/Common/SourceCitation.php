<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Common\SourceCitation\SourceCitationStructure;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\Common\SourceCitation\Data;
use MagicSunday\Gedcom\Parser\Common\SourceCitation\Event;

/**
 * A SOUR parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceCitation extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SourceCitationStructure::TAG_PAGE => Common::class,
            SourceCitationStructure::TAG_EVEN => Event::class,
            SourceCitationStructure::TAG_DATA => Data::class,
            SourceCitationStructure::TAG_OBJE => MultimediaLink::class,
            SourceCitationStructure::TAG_NOTE => NoteStructure::class,
            SourceCitationStructure::TAG_QUAY => Common::class,
        ];
    }

    /**
     * Parses a SOUR block.
     *
     * @return SourceCitationStructure
     */
    public function parse(): SourceCitationStructure
    {
        $source = new SourceCitationStructure();
        $xref   = $this->reader->xref();

        if ($xref) {
            $source->setValue(SourceCitationStructure::TAG_XREF_SOUR, $xref);
        }

        $this->process($source);

        return $source;
    }
}
