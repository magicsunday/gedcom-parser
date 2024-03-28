<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
     * A pointer to, or a cross-reference identifier of, an OBJEct record.
     */
    public const TAG_XREF_OBJE = 'XREF:OBJE';

    /**
     * An information storage place that is ordered and arranged for preservation and reference.
     */
    public const TAG_FILE = 'FILE';

    /**
     * A description of a specific writing or other work.
     */
    public const TAG_TITL = 'TITL';

    /**
     * @return string|null
     */
    public function getXref(): ?string;

    /**
     * @return FileInterface|null
     */
    public function getFile(): ?FileInterface;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;
}
