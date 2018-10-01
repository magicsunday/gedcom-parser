<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header;

use MagicSunday\Gedcom\Interfaces\Header\NoteInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The note structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Note extends DataObject implements NoteInterface
{
    /**
     * @inheritDoc
     */
    public function getContent()
    {
        return $this->getValue(self::TAG_GEDCOM_CONTENT_DESCRIPTION);
    }
}
