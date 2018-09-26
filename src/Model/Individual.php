<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Model\Common\ChangeDate;
use MagicSunday\Gedcom\Model\Individual\Name;

/**
 * The individual model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Individual
{
    /**
     * The individual identifier.
     *
     * @var string
     */
    protected $xref;

    /**
     * A list of names.
     *
     * @var Name[]
     */
    protected $names = [];

    /**
     * The change date is intended to only record the last change to a record. Some systems may want to
     * manage the change process with more detail, but it is sufficient for GEDCOM purposes to indicate
     * the last time that a record was modified.
     *
     * @var ChangeDate
     */
    private $changeDate;

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

    /**
     * @return Name[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @param Name $name
     *
     * @return self
     */
    public function addName(Name $name): self
    {
        $this->names[] = $name;
        return $this;
    }

    /**
     * @return ChangeDate
     */
    public function getChangeDate(): ChangeDate
    {
        return $this->changeDate;
    }

    /**
     * @param ChangeDate $changeDate
     *
     * @return self
     */
    public function setChangeDate(ChangeDate $changeDate): self
    {
        $this->changeDate = $changeDate;
        return $this;
    }
}
