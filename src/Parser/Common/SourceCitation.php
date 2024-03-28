<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLinkInterface;
use MagicSunday\Gedcom\Interfaces\Common\NoteInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitation\SourceCitationStructureInterface;
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
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            SourceCitationStructureInterface::TAG_PAGE => Common::class,
            SourceCitationStructureInterface::TAG_EVEN => Event::class,
            SourceCitationStructureInterface::TAG_DATA => Data::class,
            MultimediaLinkInterface::TAG_OBJE          => MultimediaLink::class,
            NoteInterface::TAG_NOTE                    => NoteStructure::class,
            SourceCitationStructureInterface::TAG_QUAY => Common::class,
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
            $source->setValue(SourceCitationStructureInterface::TAG_XREF_SOUR, $xref);
        }

        $this->process($source);

        return $source;
    }
}
