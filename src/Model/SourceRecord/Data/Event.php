<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\SourceRecord\Data;

use MagicSunday\Gedcom\Interfaces\SourceRecord\Data\EventInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The SOUR (source), DATA (data), EVEN (event) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Event extends DataObject implements EventInterface
{
    /**
     * {@inheritDoc}
     */
    public function getEventsRecorded(): ?string
    {
        return $this->getValue(self::TAG_EVENTS_RECORDED);
    }

    /**
     * {@inheritDoc}
     */
    public function getDatePeriod(): ?string
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getPlaceName(): ?string
    {
        return $this->getValue(self::TAG_PLAC);
    }
}
