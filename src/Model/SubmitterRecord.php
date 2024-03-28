<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\SubmitterRecordInterface;
use MagicSunday\Gedcom\Traits\Common\AddressStructureTrait;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\MultimediaLinkTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The SUBM (submitter) record.
 *
 * The submitter record identifies an individual or organization that contributed information contained
 * in the GEDCOM transmission. All records in the transmission are assumed to be submitted by the
 * SUBMITTER referenced in the HEADer, unless a SUBMitter reference inside a specific record
 * points at a different SUBMITTER record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SubmitterRecord extends DataObject implements SubmitterRecordInterface
{
    use AddressStructureTrait;
    use ChangeDateTrait;
    use MultimediaLinkTrait;
    use NoteTrait;

    /**
     * {@inheritDoc}
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_SUBM);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->getValue(self::TAG_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getLanguage(): array
    {
        return $this->getArrayValue(self::TAG_LANG);
    }

    /**
     * {@inheritDoc}
     */
    public function getRegisterNumber(): ?string
    {
        return $this->getValue(self::TAG_RFN);
    }

    /**
     * {@inheritDoc}
     */
    public function getRecordIdNumber(): ?string
    {
        return $this->getValue(self::TAG_RIN);
    }
}
