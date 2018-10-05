<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\SubmitterRecordInterface;
use MagicSunday\Gedcom\Traits\Common\AddressStructure;
use MagicSunday\Gedcom\Traits\Common\ChangeDate;
use MagicSunday\Gedcom\Traits\Common\MultimediaLink;
use MagicSunday\Gedcom\Traits\Common\Note;

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
    use AddressStructure;
    use ChangeDate;
    use MultimediaLink;
    use Note;

    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_SUBM);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getValue(self::TAG_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getLanguage()
    {
        return $this->getValue(self::TAG_LANG);
    }

    /**
     * @inheritDoc
     */
    public function getRegisterNumber()
    {
        return $this->getValue(self::TAG_RFN);
    }

    /**
     * @inheritDoc
     */
    public function getRecordIdNumber()
    {
        return $this->getValue(self::TAG_RIN);
    }
}
