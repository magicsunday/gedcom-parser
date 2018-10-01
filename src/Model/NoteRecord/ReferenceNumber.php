<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\NoteRecord;

use MagicSunday\Gedcom\Interfaces\NoteRecord\ReferenceNumberInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The REFN structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class ReferenceNumber extends DataObject implements ReferenceNumberInterface
{
    /**
     * @inheritDoc
     */
    public function getNumber()
    {
        return $this->getValue(self::TAG_USER_REFERENCE_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getValue(self::TAG_TYPE);
    }
}
