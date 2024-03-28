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
use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\MultimediaLinkStructureInterface;
use MagicSunday\Gedcom\Model\Common\MultimediaLink\MultimediaLinkStructure;
use MagicSunday\Gedcom\Parser\Common;
use MagicSunday\Gedcom\Parser\Common\MultimediaLink\File;

/**
 * A OBJE parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class MultimediaLink extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            MultimediaLinkStructureInterface::TAG_FILE => File::class,
            MultimediaLinkStructureInterface::TAG_TITL => Common::class,
        ];
    }

    /**
     * Parses a OBJE block.
     *
     * @return MultimediaLinkStructure
     */
    public function parse(): MultimediaLinkStructure
    {
        $multimedia = new MultimediaLinkStructure();
        $xref       = $this->reader->xref();

        if ($xref) {
            $multimedia->setValue(MultimediaLinkStructureInterface::TAG_XREF_OBJE, $xref);
        } else {
            $this->process($multimedia);
        }

        return $multimedia;
    }
}
