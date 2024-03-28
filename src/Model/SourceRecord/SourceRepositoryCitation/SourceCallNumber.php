<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\SourceRecord\SourceRepositoryCitation;

use MagicSunday\Gedcom\Interfaces\SourceRecord\SourceRepositoryCitation\SourceCallNumberInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The SOUR (source), REPO (repository), CALN (call number) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceCallNumber extends DataObject implements SourceCallNumberInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNumber(): ?string
    {
        return $this->getValue(self::TAG_SOURCE_CALL_NUMBER);
    }

    /**
     * {@inheritDoc}
     */
    public function getMediaType(): ?string
    {
        return $this->getValue(self::TAG_MEDI);
    }
}
