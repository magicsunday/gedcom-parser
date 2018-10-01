<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

/**
 * The source structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Source
{
    /**
     * The identifier.
     *
     * @var string
     */
    private $xref;

    /**
     * Returns the XREF.
     *
     * @return string
     */
    public function getXref(): string
    {
        return $this->xref;
    }

    /**
     * Sets the XREF.
     *
     * @param string $xref The XREF
     *
     * @return self
     */
    public function setXref(string $xref): self
    {
        $this->xref = $xref;
        return $this;
    }
}
