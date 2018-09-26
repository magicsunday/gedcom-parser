<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom\Model\Header\Source;

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
     * @var Data
     */
    private $name;

    /**
     * The address structure.
     *
     * @var string
     */
    private $data;

    /**
     * The publication date.
     *
     * @var Date
     */
    private $date;

    /**
     * The copyright.
     *
     * @var string
     */
    private $copyright;
}
