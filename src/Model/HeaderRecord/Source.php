<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\SourceInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The source structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Source extends DataObject implements SourceInterface
{
    /**
     * @inheritDoc
     */
    public function getApprovedSystemId()
    {
        return $this->getValue(self::TAG_APPROVED_SYSTEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function getVersion()
    {
        return $this->getValue(self::TAG_VERS);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getValue(self::TAG_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getCorporation()
    {
        return $this->getValue(self::TAG_CORP);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->getValue(self::TAG_DATA);
    }
}
