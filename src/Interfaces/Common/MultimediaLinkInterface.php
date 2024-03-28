<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

use MagicSunday\Gedcom\Interfaces\Common\MultimediaLink\MultimediaLinkStructureInterface;

/**
 * The OBJE tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface MultimediaLinkInterface
{
    /**
     * Pertaining to a grouping of attributes used in describing something. Usually referring to the data
     * required to represent a multimedia object, such an audio recording, a photograph of a person, or an
     * image of a document.
     */
    public const TAG_OBJE = 'OBJE';

    /**
     * @return MultimediaLinkStructureInterface[]
     */
    public function getObject(): array;
}
