<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common\MultimediaLink;

/**
 * The OBJE (object) structure tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface MultimediaLinkStructureInterface
{
    /**
     * A pointer to, or a cross-reference identifier of, a OBJEct record.
     */
    const TAG_XREF_OBJE = 'XREF:OBJE';

    /**
     * An information storage place that is ordered and arranged for preservation and reference.
     */
    const TAG_FILE = 'FILE';

    /**
     * A description of a specific writing or other work.
     */
    const TAG_TITL = 'TITL';

    /**
     * @return null|string
     */
    public function getXref();

    /**
     * @return null|FileInterface
     */
    public function getFile();

    /**
     * @return null|string
     */
    public function getTitle();
}
