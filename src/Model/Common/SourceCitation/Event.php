<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\SourceCitation;

use MagicSunday\Gedcom\Interfaces\Common\SourceCitation\EventInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The SOUR (source citation) EVEN (event) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Event extends DataObject implements EventInterface
{
    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getRole()
    {
        return $this->getValue(self::TAG_ROLE);
    }
}
