<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header\Source;

use MagicSunday\Gedcom\Model\Common\DateExact;

/**
 * The data structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Data
{
    /**
     * The name of the electronic data source that was used to obtain the data in this transmission.
     *
     * @var string
     */
    private $name;

    /**
     * The date this source was published or created.
     *
     * @var DateExact
     */
    private $date;

    /**
     * A copyright statement required by the owner of data from which this information was downloaded.
     *
     * @var string
     */
    private $copyright;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return DateExact
     */
    public function getDate(): DateExact
    {
        return $this->date;
    }

    /**
     * @param DateExact $date
     *
     * @return self
     */
    public function setDate(DateExact $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getCopyright(): string
    {
        return $this->copyright;
    }

    /**
     * @param string $copyright
     *
     * @return self
     */
    public function setCopyright(string $copyright): self
    {
        $this->copyright = $copyright;
        return $this;
    }
}
