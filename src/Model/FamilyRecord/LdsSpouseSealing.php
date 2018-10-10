<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\FamilyRecord;

use MagicSunday\Gedcom\Interfaces\FamilyRecord\LdsSpouseSealingInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\Note;
use MagicSunday\Gedcom\Traits\Common\SourceCitation;

/**
 * The LDS spouse sealing.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class LdsSpouseSealing extends DataObject implements LdsSpouseSealingInterface
{
    use Note;
    use SourceCitation;

    /**
     * @inheritDoc
     */
    public function getDate()
    {
        return $this->getValue(self::TAG_DATE);
    }

    /**
     * @inheritDoc
     */
    public function getTempleCode()
    {
        return $this->getValue(self::TAG_TEMP);
    }

    /**
     * @inheritDoc
     */
    public function getPlace()
    {
        return $this->getValue(self::TAG_PLAC);
    }

    /**
     * @inheritDoc
     */
    public function getDateStatus()
    {
        return $this->getValue(self::TAG_STAT);
    }
}
