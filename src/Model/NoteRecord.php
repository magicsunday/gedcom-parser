<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\NoteRecordInterface;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\SourceCitationTrait;

/**
 * The NOTE record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class NoteRecord extends DataObject implements NoteRecordInterface
{
    use ChangeDateTrait;
    use SourceCitationTrait;

    /**
     * {@inheritDoc}
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_NOTE);
    }

    /**
     * {@inheritDoc}
     */
    public function getText(): ?string
    {
        return $this->getValue(self::TAG_SUBMITTER_TEXT);
    }

    /**
     * {@inheritDoc}
     */
    public function getReferenceNumber(): array
    {
        return $this->getArrayValue(self::TAG_REFN);
    }

    /**
     * {@inheritDoc}
     */
    public function getRecordIdNumber(): ?string
    {
        return $this->getValue(self::TAG_RIN);
    }
}
