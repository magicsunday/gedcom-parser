<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\SourceRecord;

use MagicSunday\Gedcom\Interfaces\SourceRecord\SourceRepositoryCitationInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The SOUR (source), REPO (repository) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceRepositoryCitation extends DataObject implements SourceRepositoryCitationInterface
{
    use NoteTrait;

    /**
     * {@inheritDoc}
     */
    public function getXref(): ?string
    {
        return $this->getValue(self::TAG_XREF_REPO);
    }

    /**
     * {@inheritDoc}
     */
    public function getCallNumber(): array
    {
        return $this->getArrayValue(self::TAG_CALN);
    }
}
