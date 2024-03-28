<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Common;

use MagicSunday\Gedcom\Interfaces\Common\ReferenceNumberInterface;
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
     * {@inheritDoc}
     */
    public function getNumber(): ?string
    {
        return $this->getValue(self::TAG_USER_REFERENCE_NUMBER);
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): ?string
    {
        return $this->getValue(self::TAG_TYPE);
    }
}
