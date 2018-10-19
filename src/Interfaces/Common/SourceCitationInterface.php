<?php
/**
 * See LICENSE.md file for further details.
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
    const TAG_SOUR = 'SOUR';

    /**
     * @return SourceCitationStructureInterface[]
     */
    public function getSource(): array;
}
