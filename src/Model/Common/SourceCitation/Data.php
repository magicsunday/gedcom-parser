<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common\SourceCitation;

use MagicSunday\Gedcom\Interfaces\Common\SourceCitation\DataInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The SOUR (source citation) DATA (data) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Data extends DataObject implements DataInterface
{
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
    public function getText(): array
    {
        return $this->getArrayValue(self::TAG_TEXT);
    }
}
