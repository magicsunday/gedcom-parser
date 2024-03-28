<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\NoteInterface;
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
     * {@inheritDoc}
     */
    public function getContent(): ?string
    {
        return $this->getValue(self::TAG_GEDCOM_CONTENT_DESCRIPTION);
    }
}
