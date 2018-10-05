<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\SubmitterInterface;
use MagicSunday\Gedcom\Traits\Common\AddressStructure;
use MagicSunday\Gedcom\Traits\Common\ChangeDate;
use MagicSunday\Gedcom\Traits\Common\MultimediaLink;
use MagicSunday\Gedcom\Traits\Common\Note;

/**
 * The SUBM (submitter) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Submitter extends DataObject implements SubmitterInterface
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
