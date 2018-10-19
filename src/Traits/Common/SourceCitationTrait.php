<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\Common;

use MagicSunday\Gedcom\Interfaces\Common\SourceCitation\SourceCitationStructureInterface;
use MagicSunday\Gedcom\Interfaces\Common\SourceCitationInterface;

/**
 * The source citation methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait SourceCitationTrait
{
    /**
     * @param string $key
     *
     * @return array
     */
    abstract public function getArrayValue(string $key): array;

    /**
     * @return SourceCitationStructureInterface[]
     */
    public function getSource(): array
    {
        return $this->getArrayValue(SourceCitationInterface::TAG_SOUR);
    }
}
