<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\GedcomInfoInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The GEDCOM information structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class GedcomInfo extends DataObject implements GedcomInfoInterface
{
    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return $this->getValue(self::TAG_VERS);
    }

    /**
     * @inheritDoc
     */
    public function getForm(): string
    {
        return $this->getValue(self::TAG_FORM);
    }
}
