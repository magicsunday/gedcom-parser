<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Model;

/**
 * A source interface.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SourceInterface
{
    /**
     * Adds a source to the source list.
     *
     * @param Source $source The source to add
     *
     * @return self
     */
    public function addSource(Source $source): self;
}
