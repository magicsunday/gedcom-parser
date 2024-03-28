<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\Common;

use MagicSunday\Gedcom\Interfaces\Common\SourceCitation\SourceCitationStructureInterface;

/**
 * The SOUR (source citation) tag.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SourceCitationInterface
{
    /**
     * A list of sources assigned to the name.
     */
    public const TAG_SOUR = 'SOUR';

    /**
     * @return SourceCitationStructureInterface[]
     */
    public function getSource(): array;
}
