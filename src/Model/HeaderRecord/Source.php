<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\HeaderRecord;

use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\CorporationInterface;
use MagicSunday\Gedcom\Interfaces\HeaderRecord\Source\DataInterface;
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
     * {@inheritDoc}
     */
    public function getApprovedSystemId(): string
    {
        return $this->getValue(self::TAG_APPROVED_SYSTEM_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion(): ?string
    {
        return $this->getValue(self::TAG_VERS);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->getValue(self::TAG_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getCorporation(): ?CorporationInterface
    {
        return $this->getValue(self::TAG_CORP);
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ?DataInterface
    {
        return $this->getValue(self::TAG_DATA);
    }
}
