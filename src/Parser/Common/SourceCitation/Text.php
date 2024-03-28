<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\Common\SourceCitation;

use MagicSunday\Gedcom\AbstractParser;

/**
 * The SOUR-DATA-TEXT parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Text extends AbstractParser
{
    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [];
    }

    /**
     * Parses a SOUR-DATA-TEXT block.
     *
     * @return string|null
     */
    public function parse(): ?string
    {
        return $this->readContent();
    }
}
